<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Metrics\SwagPickupRate;
use App\Nova\Metrics\TotalCollections;
use App\Nova\Metrics\ShirtSizeBreakdown;
use App\Nova\Metrics\PaymentMethodBreakdown;

class DuesPackage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\DuesPackage';

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Dues Packages';
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Dues Package';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Boolean::make('Active', 'is_active')
                ->sortable()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            DateTime::make('Start Date', 'effective_start')
                ->hideFromIndex()
                ->rules('required'),

            DateTime::make('End Date', 'effective_end')
                ->hideFromIndex()
                ->rules('required'),

            Currency::make('Cost')
                ->sortable()
                ->format('%.2n')
                ->rules('required'),

            Boolean::make('Available for Purchase')
                ->sortable(),

            new Panel('Swag', $this->swagFields()),

            HasMany::make('DuesTransactions')->canSee(function ($request) {
                if ($request->resourceId == $request->user()->id) {
                    return $request->user()->can('read-dues-transactions-own');
                } else {
                    return $request->user()->can('read-dues-transactions');
                }
            }),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    protected function swagFields()
    {
        return [
            Boolean::make('Eligible for T-Shirt', 'eligible_for_shirt')
                ->hideFromIndex(),

            Boolean::make('Eligible for Polo')
                ->hideFromIndex(),
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
        return [
            (new TotalCollections())
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-payments');
                }),
            (new PaymentMethodBreakdown())
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-payments');
                }),
            (new SwagPickupRate('shirt'))
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-dues-transactions');
                }),
            (new SwagPickupRate('polo'))
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('shirt'))
                ->canSee(function ($request) {
                    return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('polo'))
                ->canSee(function ($request) {
                    return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('shirt'))
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-dues-transactions');
                }),
            (new ShirtSizeBreakdown('polo'))
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-dues-transactions');
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
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
        return [];
    }
}
