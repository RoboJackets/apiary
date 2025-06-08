<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference

namespace App\Nova;

use App\Nova\Actions\Payments\RecordPaymentActions;
use App\Rules\MatrixItineraryBusinessPolicy;
use App\Rules\MatrixItineraryDataStructure;
use App\Util\Matrix;
use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\FormData;
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
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

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
     * Get the displayable label of the resource.
     */
    #[\Override]
    public static function label(): string
    {
        return 'Trip Assignments';
    }

    /**
     * Get the URI key for the resource.
     */
    #[\Override]
    public static function uriKey(): string
    {
        return 'trip-assignments';
    }

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Member', 'user', User::class)
                ->withoutTrashed()
                ->searchable()
                ->rules('required', 'unique:travel_assignments,user_id,NULL,id,travel_id,'.$request->travel)
                ->readonly(static fn (NovaRequest $request): bool => $request->editMode === 'update')
                ->help(view('nova.help.travel.assignment.member')->render()),

            BelongsTo::make('Trip', 'travel', Travel::class)
                ->withoutTrashed()
                ->rules(
                    'required',
                    'unique:travel_assignments,travel_id,NULL,id,user_id,'.$request->user,
                    static function (string $attribute, mixed $value, Closure $fail): void {
                        $trip = \App\Models\Travel::where('id', '=', $value)->sole();

                        if ($trip->status !== 'draft' && ! Auth::user()->hasRole('admin')) {
                            $fail('Assignments cannot be created for this trip because it is not in draft status.');
                        }
                    }
                )
                ->readonly(static fn (NovaRequest $request): bool => $request->editMode === 'update')
                ->help(view('nova.help.travel.assignment.trip')->render()),

            Code::make('Matrix Itinerary')
                ->json()
                ->dependsOn(
                    ['travel'],
                    static function (Code $field, NovaRequest $request, FormData $formData): void {
                        if (
                            self::showItineraryOnForms($formData->travel) ||
                            (
                                $request->viaResource === Travel::uriKey() &&
                                self::showItineraryOnForms($request->viaResourceId)
                            )
                        ) {
                            $field->show()
                                ->rules('nullable', 'json', new MatrixItineraryDataStructure());
                        }
                    }
                )
                ->required()
                ->hide()
                ->showOnDetail(fn (): bool => $this->showItineraryOnDetail())
                ->help(view('nova.help.travel.assignment.matrixitinerary')->render()),

            Text::make(
                'Matrix Itinerary Preview',
                static fn (
                    \App\Models\TravelAssignment $assignment
                ): ?string => $assignment->matrix_itinerary === null ?
                    null :
                    view(
                        'travel.matrixitinerarypreview',
                        [
                            'itinerary' => $assignment->matrix_itinerary,
                        ]
                    )->render()
            )
                ->asHtml()
                ->onlyOnDetail()
                ->showOnDetail(fn (): bool => $this->showItineraryOnDetail()),

            Currency::make(
                'Airfare Cost',
                static fn (\App\Models\TravelAssignment $assignment): ?float => Matrix::getHighestDisplayPrice(
                    $assignment->matrix_itinerary
                )
            )
                ->showOnDetail(fn (): bool => $this->showItineraryOnDetail()),

            Boolean::make('Forms Received', 'tar_received')
                ->sortable()
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->showOnDetail(fn (): bool => $this->showDocuSignEnvelopesOnDetail()),

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
                ->onlyOnDetail()
                ->showOnDetail(fn (): bool => $this->travel->fee_amount > 0),

            Boolean::make('Paid', 'is_paid')
                ->onlyOnIndex(),

            Boolean::make(
                'Emergency Contact',
                static fn (
                    \App\Models\TravelAssignment $assignment
                ): bool => $assignment->user->has_emergency_contact_information
            ),

            MorphMany::make('DocuSign Envelopes', 'envelope', DocuSignEnvelope::class)
                ->onlyOnDetail()
                ->showOnDetail(fn (): bool => $this->showDocuSignEnvelopesOnDetail()),

            MorphMany::make('Payments', 'payment', Payment::class)
                ->onlyOnDetail()
                ->showOnDetail(fn (): bool => $this->travel->fee_amount > 0),

            self::metadataPanel(),
        ];
    }

    /**
     * Handle any post-validation processing.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    #[\Override]
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        if ($request->travel === null || $request->matrix_itinerary === null) {
            return;
        }

        $trip = \App\Models\Travel::where('id', '=', $request->travel)->sole();

        $businessPolicy = new MatrixItineraryBusinessPolicy($trip->airfare_policy);

        $businessPolicy->validate(
            'matrix_itinerary',
            $request->matrix_itinerary,
            static function (string $message) use ($validator): void {
                $validator->errors()->add('matrix_itinerary', $message);
            }
        );
    }

    /**
     * Get the search result subtitle for the resource.
     */
    #[\Override]
    public function subtitle(): ?string
    {
        return $this->user->full_name.' | '.$this->travel->name.' | '.($this->is_paid ? 'Paid' : 'Unpaid');
    }

    private static function showItineraryOnForms(string|int|null $trip_id): bool
    {
        if ($trip_id === null) {
            return false;
        }

        $trip = \App\Models\Travel::where('id', '=', $trip_id)->sole();

        if ($trip->forms === null || ! array_key_exists(\App\Models\Travel::AIRFARE_REQUEST_FORM_KEY, $trip->forms)) {
            return false;
        }

        return $trip->forms[\App\Models\Travel::AIRFARE_REQUEST_FORM_KEY];
    }

    private function showItineraryOnDetail(): bool
    {
        if (
            $this->travel->forms === null ||
            ! array_key_exists(\App\Models\Travel::AIRFARE_REQUEST_FORM_KEY, $this->travel->forms)
        ) {
            return false;
        }

        return $this->travel->forms[\App\Models\Travel::AIRFARE_REQUEST_FORM_KEY];
    }

    private function showDocuSignEnvelopesOnDetail(): bool
    {
        return $this->travel->forms !== null &&
            in_array(true, $this->travel->forms, true);
    }
}
