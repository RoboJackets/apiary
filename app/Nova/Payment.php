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
        $payment_methods = [
            'cash' => 'Cash',
            'squarecash' => 'Square Cash',
            'check' => 'Check',
            'swipe' => 'Swiped Card',
            'square' => 'Square',
        ];

        return [
            ID::make()->sortable(),

            MorphTo::make('Paid For', 'payable')
                ->types([
                    DuesTransaction::class,
                ]),

            Select::make('Payment Method', 'method')
                ->options($payment_methods)
                ->displayUsingLabels()
                ->hideFromIndex(),

            Currency::make('Amount')
                ->sortable()
                ->format('%.2n')
                ->rules('required'),

            Currency::make('Processing Fee')
                ->sortable()
                ->format('%.2n')
                ->rules('required'),

            BelongsTo::make('Recorded By', 'user', 'App\Nova\User')
                ->help('The user that recorded the payment'),

            TextArea::make('Notes')
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
