<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use App\Nova\DuesTransaction;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;

class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Payment';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

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

            new Panel('Payment Method', $this->methodFields()),

            new Panel('Square Details', $this->detailsFields()),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    protected function basicFields()
    {
        return [
            MorphTo::make('Payable')->types([
                DuesTransaction::class,
            ]),

            Currency::make('Amount'),

            Currency::make('Processing Fee')
                ->onlyOnDetail(),

            TextArea::make('Notes')
                ->onlyOnDetail(),
        ];
    }

    protected function methodFields()
    {
        return [
            Text::make('Payment Method', 'method_presentation'),

            BelongsTo::make('Recorded By', 'user', 'App\Nova\User'),
        ];
    }

    protected function detailsFields()
    {
        return [
            Text::make('Checkout ID')
                ->onlyOnDetail(),

            Text::make('Client Transaction ID', 'client_txn_id')
                ->onlyOnDetail(),

            Text::make('Server Transaction ID', 'server_txn_id')
                ->onlyOnDetail(),

            Text::make('Unique ID')
                ->onlyOnDetail(),
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
