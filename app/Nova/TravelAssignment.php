<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\TravelAssignment as AppModelsTravelAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for travel assignments.
 *
 * @extends \App\Nova\Resource<\App\Models\TravelAssignment>
 */
class TravelAssignment extends Resource
{
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

            MorphMany::make('Payments', 'payment', Payment::class)
                ->onlyOnDetail(),

            MorphMany::make('DocuSign Envelopes', 'envelope', DocuSignEnvelope::class)
                ->onlyOnDetail(),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            (new Actions\AddPayment())->canSee(static function (Request $request): bool {
                $assignment = AppModelsTravelAssignment::find($request->resourceId);

                if ($assignment !== null && is_a($assignment, AppModelsTravelAssignment::class)) {
                    if ($assignment->user->id === $request->user()->id) {
                        return false;
                    }

                    if ($assignment->is_paid) {
                        return false;
                    }

                    if (! $assignment->user->signed_latest_agreement) {
                        return false;
                    }
                }

                return $request->user()->can('create-payments');
            })->canRun(
                static fn (NovaRequest $request, AppModelsTravelAssignment $assignment): bool => $request->user()->can(
                    'create-payments'
                )
                        && ($assignment->user()->first()->id !== $request->user()->id)
            )->confirmButtonText('Add Payment'),
        ];
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        return $this->user->full_name.' | '.$this->travel->name.' | '.($this->is_paid ? 'Paid' : 'Unpaid');
    }
}
