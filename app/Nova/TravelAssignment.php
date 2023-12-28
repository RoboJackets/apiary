<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference

namespace App\Nova;

use App\Nova\Actions\Payments\RecordPaymentActions;
use App\Rules\MatrixItineraryBusinessPolicy;
use App\Rules\MatrixItineraryDataStructure;
use App\Util\BusinessTravelPolicy;
use App\Util\Matrix;
use Carbon\Carbon;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for travel assignments.
 *
 * @extends \App\Nova\Resource<\App\Models\TravelAssignment>
 */
class TravelAssignment extends Resource
{
    use RecordPaymentActions;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\TravelAssignment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Travel';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
    ];

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 2;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'travel',
        'user',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Member', 'user', User::class)
                ->withoutTrashed()
                ->searchable()
                ->rules('required', 'unique:travel_assignments,user_id,NULL,id,travel_id,'.$request->travel),

            BelongsTo::make('Travel', 'travel', Travel::class)
                ->withoutTrashed()
                ->rules('required', 'unique:travel_assignments,travel_id,NULL,id,user_id,'.$request->user)
                ->default(
                    static fn (NovaRequest $request): ?int => \App\Models\Travel::whereDate(
                        'departure_date',
                        '>=',
                        Carbon::now()
                    )
                        ->orderBy('departure_date')
                        ->first()
                            ?->id
                ),

            Code::make('Matrix Itinerary')
                ->json()
                ->rules('nullable', 'json', new MatrixItineraryDataStructure())
                ->required()
                ->help(
                    'If this trip includes airfare, you must provide an itinerary in Matrix JSON format. Search '.
                    'for flights at <a href="https://matrix.itasoftware.com">Matrix</a>, click <strong>Copy itineray '.
                    'as JSON</strong>, then paste into the text box above.'
                ),

            Text::make(
                'Matrix Itinerary Preview',
                static fn (\App\Models\TravelAssignment $assignment): string => view(
                    'travel.matrixitinerarypreview',
                    [
                        'itinerary' => $assignment->matrix_itinerary,
                    ]
                )->render()
            )
                ->asHtml()
                ->onlyOnDetail(),

            Currency::make(
                'Airfare Cost',
                static fn (\App\Models\TravelAssignment $assignment): ?float => Matrix::getHighestDisplayPrice(
                    // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
                    json_encode($assignment->matrix_itinerary)
                )
            ),

            Boolean::make('Travel Authority Request Received', 'tar_received')
                ->sortable()
                ->hideWhenCreating(),

            Currency::make('Payment Due', function (): ?int {
                // @phan-suppress-next-line PhanPluginNonBoolBranch
                if ($this->is_paid) {
                    return null;
                }

                if ($this->travel === null) {
                    return null;
                }

                return $this->travel->fee_amount;
            })
                ->onlyOnDetail(),

            Boolean::make('Paid', 'is_paid')
                ->onlyOnIndex(),

            MorphMany::make('DocuSign Envelopes', 'envelope', DocuSignEnvelope::class)
                ->onlyOnDetail(),

            MorphMany::make('Payments', 'payment', Payment::class)
                ->onlyOnDetail(),

            self::metadataPanel(),
        ];
    }

    /**
     * Handle any post-validation processing.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        if ($request->travel === null || $request->matrix_itinerary === null) {
            return;
        }

        $trip = \App\Models\Travel::where('id', '=', $request->travel)->sole();

        $businessPolicy = new MatrixItineraryBusinessPolicy($trip->airfare_policy);

        $businessPolicyPassed = true;

        $businessPolicy->validate(
            'matrix_itinerary',
            $request->matrix_itinerary,
            static function (string $message) use ($validator, &$businessPolicyPassed): void {
                $businessPolicyPassed = false;
                $validator->errors()->add('matrix_itinerary', $message);
            }
        );

        if ($businessPolicyPassed) {
            $airfare_cost = Matrix::getHighestDisplayPrice($request->matrix_itinerary);

            if ($airfare_cost === null) {
                $validator->errors()->add('matrix_itinerary', 'Internal error determining price for itinerary');
            }

            $total_cost = $trip->tar_lodging + $trip->tar_registration + $airfare_cost;

            if ($trip->fee_amount / $total_cost < BusinessTravelPolicy::TRIP_FEE_COST_RATIO) {
                $validator->errors()->add(
                    'matrix_itinerary',
                    'Trip fee must be at least '.
                    (BusinessTravelPolicy::TRIP_FEE_COST_RATIO * 100).
                    '% of the per-person cost for this trip.'
                );
            }
        }
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        return $this->user->full_name.' | '.$this->travel->name.' | '.($this->is_paid ? 'Paid' : 'Unpaid');
    }
}
