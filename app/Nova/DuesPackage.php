<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class DuesPackage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\DuesPackage';

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
            new Panel('Basic Information', $this->basicFields()),

            new Panel('Swag', $this->swagFields()),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    protected function basicFields()
    {
        return [
            Text::make('Name')
                ->sortable(),

            Boolean::make('Active', 'is_active')
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            DateTime::make('Start Date', 'effective_start')
                ->hideFromIndex(),

            DateTime::make('End Date', 'effective_end')
                ->hideFromIndex(),

            Number::make('Cost')
                ->sortable()
                ->min(0)
                ->step(0.01),

            Boolean::make('Available for Purchase')
                ->sortable(),
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
