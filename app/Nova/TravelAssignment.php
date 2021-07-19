<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\TravelAssignment as AppModelsTravelAssignment;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\MorphMany;

/**
 * A Nova resource for travel assignments.
 *
 * @property bool $is_paid
 * @property \App\Models\Travel $travel
 * @property \App\Models\User $user
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
    public function fields(Request $request): array
    {
        return [
            BelongsTo::make('Member', 'user', User::class)
                ->withoutTrashed()
                ->searchable(),

            BelongsTo::make('Travel', 'travel', Travel::class)
                ->withoutTrashed()
                ->searchable(),

            Boolean::make('Documents Received')
                ->sortable(),

            Currency::make('Payment Due', function (): ?int {
                if ($this->is_paid) {
                    return null;
                }

                if (null === $this->travel) {
                    return null;
                }

                return $this->travel->fee_amount;
            })
                ->onlyOnDetail(),

            Boolean::make('Paid', 'is_paid')
                ->onlyOnIndex(),

            MorphMany::make('Payments', 'payment', Payment::class)
                ->onlyOnDetail(),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new Actions\AddPayment())->canSee(static function (Request $request): bool {
                $assignment = AppModelsTravelAssignment::find($request->resourceId);

                if (null !== $assignment && is_a($assignment, AppModelsTravelAssignment::class)) {
                    if ($assignment->user->id === $request->user()->id) {
                        return false;
                    }

                    if ($assignment->is_paid) {
                        return false;
                    }

                    if (! $assignment->user->hasSignedLatestAgreement()) {
                        return false;
                    }
                }

                return $request->user()->can('create-payments');
            })->canRun(static function (Request $request, AppModelsTravelAssignment $assignment): bool {
                return $request->user()->can('create-payments')
                    && ($assignment->user()->first()->id !== $request->user()->id);
            })->confirmButtonText('Add Payment'),
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
