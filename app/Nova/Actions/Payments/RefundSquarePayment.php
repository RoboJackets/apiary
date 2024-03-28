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
use Square\Models\Builders\MoneyBuilder;
use Square\Models\Builders\RefundPaymentRequestBuilder;
use Square\Models\OrderState;
use Square\SquareClient;

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
     * @phan-suppress PhanTypeSuspiciousStringExpression
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

        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $retrieveOrderResponse = $square->getOrdersApi()->retrieveOrder($payment->order_id);

        if (! $retrieveOrderResponse->isSuccess()) {
            Log::error(self::class.' Error retrieving order - '.json_encode($retrieveOrderResponse->getErrors()));

            $this->markAsFailed($payment, json_encode($retrieveOrderResponse->getErrors()));

            return self::danger('Error retrieving order information from Square.');
        }

        if ($retrieveOrderResponse->getResult()->getOrder()->getState() !== OrderState::COMPLETED) {
            $this->markAsFailed($payment, 'This order is not complete.');

            return self::danger('This order is not complete.');
        }

        $paymentId = $retrieveOrderResponse->getResult()->getOrder()->getTenders()[0]->getId();

        $refundPaymentResponse = $square->getRefundsApi()->refundPayment(
            RefundPaymentRequestBuilder::init(
                $payment->unique_id,
                MoneyBuilder::init()
                    ->amount(intval(floatval($payment->amount) * 100))
                    ->currency('USD')
                    ->build()
            )
                ->paymentId($paymentId)
                ->reason($fields->reason)
                ->build()
        );

        if (! $refundPaymentResponse->isSuccess()) {
            Log::error(self::class.' Error refunding payment - '.json_encode($refundPaymentResponse->getErrors()));

            $this->markAsFailed($payment, json_encode($refundPaymentResponse->getErrors()));

            return self::danger('Error refunding payment.');
        }

        $status = $refundPaymentResponse->getResult()->getRefund()->getStatus();

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
