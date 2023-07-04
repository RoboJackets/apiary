<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition

namespace App\Nova;

use App\Models\Payment as AppModelsPayment;
use App\Models\User as AppModelsUser;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Square\Models\OrderState;
use Square\SquareClient;

/**
 * A Nova resource for payments.
 *
 * @extends \App\Nova\Resource<\App\Models\Payment>
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
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

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
    public function fields(NovaRequest $request): array
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
                ->searchable()
                ->sortable(),

            Text::make('Notes')
                ->onlyOnDetail(),

            Boolean::make('Receipt Sent')
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            ...(in_array($this->method, ['square', 'squarecash', 'swipe'], true) ? [
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

            Text::make('Order State (retrieved from Square)', function (): ?string {
                if ($this->order_id === null) {
                    return null;
                }

                return (new SquareClient(
                    [
                        'accessToken' => config('square.access_token'),
                        'environment' => config('square.environment'),
                    ]
                ))->getOrdersApi()
                    ->retrieveOrder($this->order_id)
                    ->getResult()
                    ->getOrder()
                    ->getState();
            })
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

            URL::make('Receipt URL')
                ->onlyOnDetail(),

            URL::make('Checkout URL', 'url')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            (new Actions\ResetIdempotencyKey())->canSee(static function (Request $request): bool {
                $payment = AppModelsPayment::find($request->resourceId);

                if ($payment !== null && is_a($payment, AppModelsPayment::class)) {
                    return self::canResetKey($request->user(), $payment);
                }

                return $request->user()->can('delete-payments');
            })->canRun(
                static fn (NovaRequest $r, AppModelsPayment $p): bool => self::canResetKey($r->user(), $p)
            )->confirmText(
                'Are you sure you want to reset the idempotency key for this payment? This can result in duplicate'
                .'payments and should only be used if you are sure that the associated Square order is canceled.'
            )->confirmButtonText('Reset Idempotency Key'),
            (new Actions\RefundPayment())->canSee(static function (Request $request): bool {
                $payment = AppModelsPayment::find($request->resourceId);

                if ($payment !== null && is_a($payment, AppModelsPayment::class)) {
                    return self::canRefundPayment($request->user(), $payment);
                }

                return $request->user()->can('refund-payments');
            })->canRun(
                static fn (NovaRequest $r, AppModelsPayment $p): bool => self::canRefundPayment($r->user(), $p)
            )->confirmButtonText('Refund Payment'),
        ];
    }

    private static function canResetKey(AppModelsUser $user, AppModelsPayment $payment): bool
    {
        if ($payment->amount > 0) {
            return false;
        }

        if ($payment->unique_id === null) {
            return false;
        }

        $order_id = $payment->order_id;

        if ($order_id === null) {
            return false;
        }

        if (
            (new SquareClient(
                [
                    'accessToken' => config('square.access_token'),
                    'environment' => config('square.environment'),
                ]
            ))->getOrdersApi()
                ->retrieveOrder($order_id)
                ->getResult()
                ->getOrder()
                ->getState() === OrderState::COMPLETED
        ) {
            return false;
        }

        return $user->can('delete-payments');
    }

    private static function canRefundPayment(AppModelsUser $user, AppModelsPayment $payment): bool
    {
        if (intval($payment->amount) === 0) {
            return false;
        }

        if ($payment->unique_id === null) {
            return false;
        }

        $order_id = $payment->order_id;

        if ($order_id === null) {
            return false;
        }

        if (
            (new SquareClient(
                [
                    'accessToken' => config('square.access_token'),
                    'environment' => config('square.environment'),
                ]
            ))->getOrdersApi()
                ->retrieveOrder($order_id)
                ->getResult()
                ->getOrder()
                ->getState() !== OrderState::COMPLETED
        ) {
            return false;
        }

        return $user->can('refund-payments');
    }

    public static function searchable(): bool
    {
        return false;
    }
}
