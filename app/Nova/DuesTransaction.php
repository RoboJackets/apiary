<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\DuesTransaction as ADT;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\MorphMany;

class DuesTransaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ADT::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['payment', 'package', 'user'];

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Dues Transactions';
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel(): string
    {
        return 'Dues Transaction';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<mixed>
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()
                ->sortable(),

            BelongsTo::make('Paid By', 'user', 'App\\Nova\\User')
                ->searchable(),

            BelongsTo::make('Dues Package', 'package', 'App\\Nova\\DuesPackage'),

            Text::make('Status')
                ->resolveUsing(static function (string $str): string {
                    return ucfirst($str);
                })
                ->exceptOnForms(),

            Currency::make('Payment Due', function () {
                if ($this->is_paid) {
                    return;
                }

                if (null === $this->package) {
                    return;
                }

                if (! $this->package->is_active) {
                    return;
                }

                return $this->package->cost;
            })
                ->onlyOnDetail()
                ->format('%.2n'),

            Text::make('Shirt Status', 'swag_shirt_status')
                ->onlyOnIndex(),

            Text::make('Polo Status', 'swag_polo_status')
                ->onlyOnIndex(),

            new Panel(
                'T-Shirt Distribution',
                [
                    Text::make('Status', 'swag_shirt_status')
                        ->onlyOnDetail(),
                    DateTime::make('Timestamp', 'swag_shirt_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagShirtProvidedBy', User::class)
                        ->help('The user that recorded the payment')
                        ->onlyOnDetail(),
                ]
            ),

            new Panel(
                'Polo Distribution',
                [
                    Text::make('Status', 'swag_polo_status')
                        ->onlyOnDetail(),
                    DateTime::make('Timestamp', 'swag_polo_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagPoloProvidedBy', User::class)
                        ->help('The user that recorded the payment')
                        ->onlyOnDetail(),
                ]
            ),

            MorphMany::make('Payments', 'payment', 'App\Nova\Payment')
                ->onlyOnDetail(),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    /**
     * Timestamp fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function metaFields(): array
    {
        return [
            DateTime::make('Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
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
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new Actions\DistributeShirt())->canSee(static function (Request $request): bool {
                $transaction = \App\DuesTransaction::find($request->resourceId);

                if (null !== $transaction) {
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
            })->canRun(static function (Request $request, ADT $dues_transaction): bool {
                return $request->user()->can('distribute-swag');
            }),
            (new Actions\DistributePolo())->canSee(static function (Request $request): bool {
                $transaction = \App\DuesTransaction::find($request->resourceId);

                if (null !== $transaction) {
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
            })->canRun(static function (Request $request, ADT $dues_transaction): bool {
                return $request->user()->can('distribute-swag');
            }),
            (new Actions\AddPayment())->canSee(static function (Request $request): bool {
                $transaction = \App\DuesTransaction::find($request->resourceId);

                if (null !== $transaction) {
                    if ($transaction->user()->get()->first()->id === $request->user()->id) {
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
            })->canRun(static function (Request $request, ADT $dues_transaction): bool {
                return $request->user()->can('create-payments')
                    && ($dues_transaction->user()->get()->first()->id !== $request->user()->id);
            }),
        ];
    }
}
