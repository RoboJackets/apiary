<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
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
    public static $model = 'App\\DuesTransaction';

    public static $with = ['payment', 'package', 'user'];

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Dues Transactions';
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Dues Transaction';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Paid By', 'user', 'App\\Nova\\User'),

            BelongsTo::make('Dues Package', 'package', 'App\\Nova\\DuesPackage'),

            Text::make('Status')->resolveUsing(function ($str) {
                return ucfirst($str);
            })->exceptOnForms(),

            Currency::make('Payment Due', function () {
                if ($this->is_paid) {
                    return;
                } elseif (null === $this->package) {
                    return;
                }
                return $this->package->get()->first()->cost;
            })
                ->onlyOnDetail()
                ->format('%.2n'),

            Text::make('Shirt Status', 'swag_shirt_status')
                ->onlyOnIndex(),

            Text::make('Polo Status', 'swag_polo_status')
                ->onlyOnIndex(),

            new Panel('T-Shirt Distribution',
                [
                    Text::make('Status', 'swag_shirt_status')
                        ->onlyOnDetail(),
                    DateTime::make('Timestamp', 'swag_shirt_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagShirtProvidedBy', 'App\\Nova\\User')
                        ->help('The user that recorded the payment')
                        ->onlyOnDetail(),
                ]
            ),

            new Panel('Polo Distribution',
                [
                    Text::make('Status', 'swag_polo_status')
                        ->onlyOnDetail(),
                    DateTime::make('Timestamp', 'swag_polo_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagPoloProvidedBy', 'App\\Nova\\User')
                        ->help('The user that recorded the payment')
                        ->onlyOnDetail(),
                ]
            ),

            MorphMany::make('Payments', 'payment', 'App\Nova\Payment')
                ->onlyOnDetail(),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    protected function metaFields()
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        if ($request->user()->can('read-teams-membership')) {
            return [
                new Filters\DuesTransactionTeam,
                new Filters\DuesTransactionPaymentStatus,
                new Filters\DuesTransactionSwagStatus,
            ];
        } else {
            return [
                new Filters\DuesTransactionPaymentStatus,
                new Filters\DuesTransactionSwagStatus,
            ];
        }
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new Actions\DistributeShirt)
                ->canSee(function ($request) {
                    $transaction = \App\DuesTransaction::find($request->resourceId);
                    if (null !== $transaction) {
                        if (! $transaction->package()->get()->first()->eligible_for_shirt) {
                            return false;
                        } elseif (! $transaction->is_paid) {
                            return false;
                        } elseif (null !== $transaction->swag_shirt_provided) {
                            return false;
                        }
                    }

                    return $request->user()->can('distribute-swag');
                })->canRun(function ($request, $dues_transaction) {
                    return $request->user()->can('distribute-swag');
                }),
            (new Actions\DistributePolo)
                ->canSee(function ($request) {
                    $transaction = \App\DuesTransaction::find($request->resourceId);
                    if (null !== $transaction) {
                        if (! $transaction->package()->get()->first()->eligible_for_polo) {
                            return false;
                        } elseif (! $transaction->is_paid) {
                            return false;
                        } elseif (null !== $transaction->swag_polo_provided) {
                            return false;
                        }
                    }

                    return $request->user()->can('distribute-swag');
                })->canRun(function ($request, $dues_transaction) {
                    return $request->user()->can('distribute-swag');
                }),
            (new Actions\AddPayment)
                ->canSee(function ($request) {
                    $transaction = \App\DuesTransaction::find($request->resourceId);
                    if (null !== $transaction) {
                        if ($transaction->user()->get()->first()->id === $request->user()->id) {
                            return false;
                        } elseif ($transaction->is_paid) {
                            return false;
                        }
                    }

                    return $request->user()->can('create-payments');
                })->canRun(function ($request, $dues_transaction) {
                    return $request->user()->can('create-payments') && ($dues_transaction->user()->get()->first()->id != $request->user()->id);
                }),
        ];
    }
}
