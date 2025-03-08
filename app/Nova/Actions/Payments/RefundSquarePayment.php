<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Square\Orders\Requests\GetOrdersRequest;
use Square\Refunds\Requests\RefundPaymentRequest;
use Square\SquareClient;
use Square\Types\Money;
use Square\Types\OrderState;

class RefundSquarePayment extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Refund Payment';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Refund Payment';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Provide a reason for refunding this payment. This will be visible to the member on the '.
        'updated Square receipt.';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The metadata for the element.
     *
     * @var array<string, bool>
     */
    public $meta = [
        'destructive' => true,
    ];

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Payment>  $models
     *
     * @phan-suppress PhanTypeMismatchPropertyProbablyReal
     * @phan-suppress PhanPossiblyFalseTypeArgument
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $payment = $models->sole();

        if ($payment->method !== 'square') {
            $this->markAsFailed($payment, Str::title($payment->method).' payments may not be refunded.');

            return self::danger(Str::title($payment->method).' payments may not be refunded.');
        }

        if (floatval($payment->amount) === 0.0) {
            $this->markAsFailed($payment, 'This payment was already refunded.');

            return self::danger('This payment was already refunded.');
        }

        if (Auth::user()->cant('refund-payments')) {
            $this->markAsFailed($payment, 'You do not have access to refund payments.');

            return self::danger('You do not have access to refund payments.');
        }

        $square = new SquareClient(
            token: config('square.access_token'),
            options: [
                'baseUrl' => config('square.base_url'),
            ]
        );

        $getOrderResponse = $square->orders->get(new GetOrdersRequest(['orderId' => $payment->order_id]));

        if ($getOrderResponse->getOrder() === null) {
            Log::error(self::class.' Error retrieving order - '.json_encode($getOrderResponse->getErrors()));

            $this->markAsFailed($payment, json_encode($getOrderResponse->getErrors()));

            return self::danger('Error retrieving order information from Square.');
        }

        if (OrderState::from($getOrderResponse->getOrder()->getState()) !== OrderState::Completed) {
            $this->markAsFailed($payment, 'This order is not complete.');

            return self::danger('This order is not complete.');
        }

        $refundPaymentResponse = $square->refunds->refundPayment(new RefundPaymentRequest([
            // @phan-suppress-next-line PhanTypeArraySuspiciousNullable
            'paymentId' => $getOrderResponse->getOrder()->getTenders()[0]->getId(),
            'reason' => $fields->reason,
            'idempotencyKey' => $payment->unique_id,
            'amountMoney' => new Money([
                'amount' => intval(floatval($payment->amount) * 100),
                'currency' => 'USD',
            ]),
        ]));

        if ($refundPaymentResponse->getRefund() === null) {
            Log::error(self::class.' Error refunding payment - '.json_encode($refundPaymentResponse->getErrors()));

            $this->markAsFailed($payment, json_encode($refundPaymentResponse->getErrors()));

            return self::danger('Error refunding payment.');
        }

        $status = $refundPaymentResponse->getRefund()->getStatus();

        if (! in_array($status, ['PENDING', 'COMPLETED'], true)) {
            Log::error(self::class.' Error refunding payment - refund status is '.$status);

            $this->markAsFailed($payment, 'Refund status is '.$status);

            return self::danger('Refund status is '.$status);
        }

        $payment->amount = 0;
        $payment->notes .= '; refunded with reason "'.$fields->reason.'"';
        $payment->save();

        return self::message('The refund is '.strtolower($status).'!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        $payment = Payment::whereId($request->resourceId ?? $request->resources)->sole();

        return [
            Currency::make('Refund Amount')
                ->default(static fn (): string => $payment->amount)
                ->required()
                ->help('Partial refunds aren\'t supported.')
                ->readonly(),

            Text::make('Reason')
                ->required()
                ->rules('required', 'max:192'),
        ];
    }
}
