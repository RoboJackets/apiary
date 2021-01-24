<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\DuesTransaction as AppModelsDuesTransaction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;

/**
 * A Nova resource for dues transactions.
 *
 * @property bool $is_paid Whether this transaction is paid for
 * @property \App\Models\DuesPackage $package The package associated with this transaction
 * @property \App\Models\User $user the user associated with this transaction
 */
class DuesTransaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AppModelsDuesTransaction::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['payment', 'package', 'user'];

    /**
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'Dues Transactions';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Dues Transaction';
    }

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Dues';

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(),

            BelongsTo::make('Paid By', 'user', User::class)
                ->searchable(),

            BelongsTo::make('Dues Package', 'package', DuesPackage::class),

            Text::make('Status')
                ->resolveUsing(static function (string $str): string {
                    return ucfirst($str);
                })
                ->exceptOnForms(),

            Currency::make('Payment Due', function (): ?float {
                if ($this->is_paid) {
                    return null;
                }

                if (null === $this->package) {
                    return null;
                }

                if (! $this->package->is_active) {
                    return null;
                }

                return $this->package->cost;
            })
                ->onlyOnDetail(),

            BelongsToMany::make('Merchandise', 'merchandise')
                ->fields(static function (): array {
                    return [
                        DateTime::make('Provided At'),

                        // I tried a BelongsTo but it appeared to be looking for the relationship on the model itself,
                        // not the pivot model. This is a temporary fallback.
                        Text::make('Provided By', 'provided_by_name'),
                    ];
                }),

            MorphMany::make('Payments', 'payment', Payment::class)
                ->onlyOnDetail(),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return $request->user()->can('read-teams-membership') ? [
            new Filters\DuesTransactionTeam(),
            new Filters\DuesTransactionPaymentStatus(),
        ] : [
            new Filters\DuesTransactionPaymentStatus(),
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
                $transaction = AppModelsDuesTransaction::find($request->resourceId);

                if (null !== $transaction && is_a($transaction, AppModelsDuesTransaction::class)) {
                    if ($transaction->user->id === $request->user()->id) {
                        return false;
                    }

                    if ($transaction->is_paid) {
                        return false;
                    }

                    if (! $transaction->package->is_active) {
                        return false;
                    }

                    if (! $transaction->user->hasSignedLatestAgreement()) {
                        return false;
                    }
                }

                return $request->user()->can('create-payments');
            })->canRun(static function (Request $request, AppModelsDuesTransaction $dues_transaction): bool {
                return $request->user()->can('create-payments')
                    && ($dues_transaction->user()->first()->id !== $request->user()->id);
            }),
        ];
    }
}
