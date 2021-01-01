<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\DuesTransaction as AppModelsDuesTransaction;
use App\Models\User as AppModelsUser;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

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

            Text::make('Shirt Status', 'swag_shirt_status')
                ->onlyOnIndex(),

            Text::make('Polo Status', 'swag_polo_status')
                ->onlyOnIndex(),

            new Panel(
                'T-Shirt Distribution',
                [
                    Text::make('Status', 'swag_shirt_status')
                        ->onlyOnDetail(),
                    Text::make('Size', function (): ?string {
                        $shirt_size = $this->user->shirt_size;

                        return null === $shirt_size ? null : AppModelsUser::$shirt_sizes[$shirt_size];
                    })->onlyOnDetail(),
                    DateTime::make('Timestamp', 'swag_shirt_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagShirtProvidedBy', User::class)
                        ->help('The user that recorded the distribution of the t-shirt')
                        ->onlyOnDetail(),
                ]
            ),

            new Panel(
                'Polo Distribution',
                [
                    Text::make('Status', 'swag_polo_status')
                        ->onlyOnDetail(),
                    Text::make('Size', function (): ?string {
                        $polo_size = $this->user->polo_size;

                        return null === $polo_size ? null : AppModelsUser::$shirt_sizes[$polo_size];
                    })->onlyOnDetail(),
                    DateTime::make('Timestamp', 'swag_polo_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagPoloProvidedBy', User::class)
                        ->help('The user that recorded the distribution of the polo')
                        ->onlyOnDetail(),
                ]
            ),

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
            new Filters\DuesTransactionSwagStatus(),
        ] : [
            new Filters\DuesTransactionPaymentStatus(),
            new Filters\DuesTransactionSwagStatus(),
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
            (new Actions\DistributeShirt())->canSee(static function (Request $request): bool {
                $transaction = AppModelsDuesTransaction::find($request->resourceId);

                if (null !== $transaction && is_a($transaction, AppModelsDuesTransaction::class)) {
                    if (! $transaction->package->eligible_for_shirt) {
                        return false;
                    }

                    if (! $transaction->is_paid) {
                        return false;
                    }

                    if (null !== $transaction->swag_shirt_provided) {
                        return false;
                    }
                }

                return $request->user()->can('distribute-swag');
            })->canRun(static function (Request $request, AppModelsDuesTransaction $dues_transaction): bool {
                return $request->user()->can('distribute-swag');
            }),
            (new Actions\DistributePolo())->canSee(static function (Request $request): bool {
                $transaction = AppModelsDuesTransaction::find($request->resourceId);

                if (null !== $transaction && is_a($transaction, AppModelsDuesTransaction::class)) {
                    if (! $transaction->package->eligible_for_polo) {
                        return false;
                    }

                    if (! $transaction->is_paid) {
                        return false;
                    }

                    if (null !== $transaction->swag_polo_provided) {
                        return false;
                    }
                }

                return $request->user()->can('distribute-swag');
            })->canRun(static function (Request $request, AppModelsDuesTransaction $dues_transaction): bool {
                return $request->user()->can('distribute-swag');
            }),
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
                }

                return $request->user()->can('create-payments');
            })->canRun(static function (Request $request, AppModelsDuesTransaction $dues_transaction): bool {
                return $request->user()->can('create-payments')
                    && ($dues_transaction->user()->first()->id !== $request->user()->id);
            }),
        ];
    }
}
