<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Payment as AppModelsPayment;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Panel;

class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Payment::class;

    /**
     * Fields to eager load on index.
     *
     * @var array<string>
     */
    public static $with = ['user'];

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()
                ->sortable(),

            MorphTo::make('Paid For', 'payable')
                ->types([
                    DuesTransaction::class,
                ]),

            Select::make('Payment Method', 'method')
                ->options(AppModelsPayment::$methods)
                ->displayUsingLabels()
                ->sortable(),

            Currency::make('Amount')
                ->sortable(),

            BelongsTo::make('Recorded By', 'user', User::class)
                ->help('The user that recorded the payment')
                ->sortable(),

            Textarea::make('Notes')
                ->onlyOnDetail()
                ->alwaysShow(),

            new Panel('Square Metadata', $this->squareFields()),

            new Panel('Timestamps', $this->metaFields()),
        ];
    }

    /**
     * Square fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function squareFields(): array
    {
        return [
            Currency::make('Processing Fee')
                ->onlyOnDetail(),

            String::make('Checkout ID')
                ->onlyOnDetail(),

            String::make('Client Transaction ID', 'client_txn_id')
                ->onlyOnDetail(),

            String::make('Server Transaction ID', 'server_txn_id')
                ->onlyOnDetail(),

            String::make('Idempotency Key', 'unique_id')
                ->onlyOnDetail(),

            String::make('Order ID')
                ->onlyOnDetail(),

            String::make('Card Brand')
                ->onlyOnDetail(),

            String::make('Card Type')
                ->onlyOnDetail(),

            String::make('Last 4')
                ->onlyOnDetail(),

            String::make('Prepaid Type')
                ->onlyOnDetail(),

            String::make('Entry Method')
                ->onlyOnDetail(),

            String::make('Statement Description')
                ->onlyOnDetail(),

            String::make('Receipt Number')
                ->onlyOnDetail(),

            String::make('Receipt URL')
                ->onlyOnDetail(),
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
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
