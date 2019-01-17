<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
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

            BelongsTo::make('Paid By', 'user', 'App\\Nova\\User')
                ->onlyOnDetail(),

            BelongsTo::make('Dues Package', 'package', 'App\\Nova\\DuesPackage')
                ->onlyOnDetail(),

            Text::make('Status')->resolveUsing(function ($str) {
                return ucfirst($str);
            })
                ->onlyOnDetail(),

            new Panel('T-Shirt Distribution',
                [
                    DateTime::make('Timestamp', 'swag_shirt_provided')
                        ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagShirtProvidedBy', 'App\\Nova\\User')
                        ->help('The user that recorded the payment')
                        ->onlyOnDetail(),
                ]
            ),

            new Panel('Polo Distribution',
                [
                    DateTime::make('Timestamp', 'swag_polo_provided')
                            ->onlyOnDetail(),
                    BelongsTo::make('Distributed By', 'swagPoloProvidedBy', 'App\\Nova\\User')
                        ->help('The user that recorded the payment')
                        ->onlyOnDetail(),
                ]
            ),

            MorphMany::make('Payments')
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
