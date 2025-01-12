<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Square\SquareClient;
use Square\Models\Builders\UpdateOrderRequestBuilder;

/**
 * If a payment record is deleted before being completed in Square, cancels payment in Square.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class PaymentDeletedListener implements ShouldQueue
{
    public function handle(PaymentDeleted $event): void
    {
        $payment = $event->payment;
        $order_id = $payment->order_id;
        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);
        $orders_api = $square->getOrdersApi();

        $parentSpan = SentrySdk::getCurrentHub()->getSpan();

        if ($parentSpan !== null) {
            $context = new SpanContext();
            $context->setOp('square.retrieve_order');    //TODO: read up on what all this Sentry stuff does. Putting here since it may be useful for if the retrieve order op fails. Not sure what it does though
            $span = $parentSpan->startChild($context);
            SentrySdk::getCurrentHub()->setSpan($span);
        }

        $retrieveOrderResponse = $orders_api->retrieveOrder($order_id);

        if ($parentSpan !== null) {
            // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
            $span->finish();
            SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if (! $retrieveOrderResponse->isSuccess()) {
            Log::error(self::class.' Error retrieving order - '.json_encode($retrieveOrderResponse->getErrors()));
            // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
            \Sentry\captureMessage(json_encode($retrieveOrderResponse->getErrors()));

            return; // TODO: What should I do after logging the error?
        }

        $order = $retrieveOrderResponse->getResult()->getOrder();

        if (
            $order === null ||
            $order->getState() === 'COMPLETED' ||
            $order->getState() === 'CANCELED'
        ) {
            Log::info(self::class.' No order found for deleted payment '.$payment->id.', or the order found was complete/canceled.');
        }

        $order->setState('CANCELED');
        $updated_order_request = UpdateOrderRequestBuilder::init()
            ->order($order)
            ->build();
        
        $cancel_response = $orders_api->updateOrder($order_id, $updated_order_request);

        if (! $cancel_response->isSuccess()) {
            Log::error(self::class.' Error updating order status - '.json_encode($cancel_response->getErrors()));
            \Sentry\captureMessage(json_encode($cancel_response->getErrors()));
            return; // TODO:  What should I do after logging this error?
        }

        Log::info(self::class.' Canceled order '.$order_id.', Status code '.$cancel_response->getStatusCode());
    }
}