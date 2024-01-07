<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Payment as AppModelsPayment;
use App\Nova\Actions\Payments\DisallowedRefund;
use App\Nova\Actions\Payments\RefundOfflinePayment;
use App\Nova\Actions\Payments\RefundSquarePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Actions\Action;
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
    public static $with = [
        'user',
        'payable',
    ];

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

            Text::make('Order State (retrieved from Square)', fn (): ?string => $this->getSquareOrderState())
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
        $resourceId = $request->resourceId ?? $request->resources;
        $user = $request->user();

        if ($resourceId === null || $user === null) {
            return [];
        }

        $payment = AppModelsPayment::find($resourceId);

        if ($payment === null || floatval($payment->amount) === 0.0) {
            return [];
        }

        if ($request->user()->cant('refund-payments')) {
            return [];
        }

        if (in_array($payment->method, RefundOfflinePayment::REFUNDABLE_OFFLINE_PAYMENT_METHODS, true)) {
            if ($payment->payable->user->id === $user->id) {
                return [
                    self::selfRefundNotAllowed(),
                ];
            }

            return [
                RefundOfflinePayment::make()
                    ->canSee(static fn (Request $request): bool => $request->user()->can('refund-payments'))
                    ->canRun(static fn (
                        NovaRequest $request,
                        AppModelsPayment $payment
                    ): bool => $request->user()->can('refund-payments')),
            ];
        }

        if ($payment->method === 'square' &&
            $payment->unique_id !== null &&
            $payment->order_id !== null &&
            $payment->created_at !== null &&
            Carbon::now()->subYear()->lessThanOrEqualTo($payment->created_at) &&
            $payment->getSquareOrderState() === OrderState::COMPLETED
        ) {
            if ($payment->payable->user->id === $user->id) {
                return [
                    self::selfRefundNotAllowed(),
                ];
            }

            return [
                RefundSquarePayment::make()
                    ->canSee(static fn (Request $request): bool => $request->user()->can('refund-payments'))
                    ->canRun(static fn (
                        NovaRequest $request,
                        AppModelsPayment $payment
                    ): bool => $request->user()->can('refund-payments')),
            ];
        }

        if ($payment->method === 'square' &&
            $payment->unique_id !== null &&
            $payment->order_id !== null &&
            $payment->created_at !== null &&
            Carbon::now()->subYear()->greaterThan($payment->created_at) &&
            $payment->getSquareOrderState() === OrderState::COMPLETED
        ) {
            return [
                self::squareTransactionTooOld(),
            ];
        }

        return [];
    }

    private static function selfRefundNotAllowed(): Action
    {
        return DisallowedRefund::make('You may not refund your own payment.')
            ->canRun(static fn (): bool => true);
    }

    private static function squareTransactionTooOld(): Action
    {
        return DisallowedRefund::make('Square transactions older than 1 year may not be refunded.')
            ->canRun(static fn (): bool => true);
    }

    public static function searchable(): bool
    {
        return false;
    }

    /**
     * Determine if this resource is available for navigation.
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->hasRole('admin');
    }
}
