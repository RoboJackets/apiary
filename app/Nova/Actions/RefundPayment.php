<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Outhebox\NovaHiddenField\HiddenField as Hidden;
use Square\Models\Money;
use Square\Models\RefundPaymentRequest;
use Square\SquareClient;

class RefundPayment extends DestructiveAction
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection<\App\Models\Payment>  $models
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (count($models) > 1) {
            return Action::danger('This action can only be run on one payment at a time.');
        }

        $refundedBy = User::where('id', $fields->refunded_by)->sole();

        $payment = $models->first();

        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $ordersApi = $square->getOrdersApi();
        $retrieveOrderResponse = $ordersApi->retrieveOrder($payment->order_id);

        if (! $retrieveOrderResponse->isSuccess()) {
            Log::error(self::class.' Error retrieving order - '.json_encode($retrieveOrderResponse->getErrors()));

            return Action::danger('Error retrieving order information from Square');
        }

        $paymentId = $retrieveOrderResponse->getOrder()->getTenders()[0]->getPaymentId();

        $money = new Money();
        $money->setAmount($payment->amount); // this includes the processing fee
        $money->setCurrency('USD');

        $refundPaymentRequest = new RefundPaymentRequest();
        $refundPaymentRequest->setIdempotencyKey($payment->unique_id);
        $refundPaymentRequest->setAmountMoney($money);
        $refundPaymentRequest->setPaymentId($paymentId);
        $refundPaymentRequest->setReason($fields->refund_reason);

        $refundsApi = $square->getRefundsApi();

        $refundPaymentResponse = $refundsApi->refundPayment($refundPaymentRequest);

        if (! $refundPaymentResponse->isSuccess()) {
            Log::error(self::class.' Error refunding payment - '.json_encode($refundPaymentResponse->getErrors()));

            return Action::danger('Error refunding payment');
        }

        $status = $refundPaymentResponse->getRefund()->getStatus();

        if (! in_array($status, ['PENDING', 'COMPLETED'], true)) {
            Log::error(self::class.' Error refunding payment - refund status is '.$status);

            return Action::danger('Refund status is '.$status);
        }

        $payment->amount = 0;
        $payment->notes = 'Payment was refunded by '.$refundedBy->full_name.' because '.$fields->refund_reason;
        $payment->save();

        return Action::message('The refund is '.strtolower($status));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [
            // not actually required in square but i think it is good practice
            Text::make('Refund Reason')
                ->required()
                ->rules('required,max:192'),

            Hidden::make('Refunded By')
                ->current_user_id(),
        ];
    }
}
