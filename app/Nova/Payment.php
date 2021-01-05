<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Payment as AppModelsPayment;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for payments.
 *
 * @property string $method
 */
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
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'checkout_id',
        'client_txn_id',
        'server_txn_id',
        'unique_id',
        'order_id',
        'notes',
        'last_4',
        'receipt_number',
        'receipt_url',
        'square_cash_transaction_id',
    ];

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

            Text::make('Notes')
                ->onlyOnDetail(),

            ...(in_array($this->method, ['square', 'squarecash', 'swiped'], true) ? [
                new Panel('Square Metadata', $this->squareFields()),
            ] : []),

            self::metadataPanel(),
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

            Text::make('Square Cash Transaction ID')
                ->onlyOnDetail(),

            Text::make('Checkout ID')
                ->onlyOnDetail(),

            Text::make('Client Transaction ID', 'client_txn_id')
                ->onlyOnDetail(),

            Text::make('Server Transaction ID', 'server_txn_id')
                ->onlyOnDetail(),

            Text::make('Idempotency Key', 'unique_id')
                ->onlyOnDetail(),

            Text::make('Order ID')
                ->onlyOnDetail(),

            Text::make('Card Brand')
                ->onlyOnDetail(),

            Text::make('Card Type')
                ->onlyOnDetail(),

            Text::make('Last 4')
                ->onlyOnDetail(),

            Text::make('Prepaid Type')
                ->onlyOnDetail(),

            Text::make('Entry Method')
                ->onlyOnDetail(),

            Text::make('Statement Description')
                ->onlyOnDetail(),

            Text::make('Receipt Number')
                ->onlyOnDetail(),

            Text::make('Receipt URL')
                ->onlyOnDetail(),
        ];
    }
}
