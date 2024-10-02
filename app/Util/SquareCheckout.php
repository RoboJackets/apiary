<?php

declare(strict_types=1);

// phpcs:disable Generic.Formatting.SpaceBeforeCast.NoSpace
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint

namespace App\Util;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Square\Models\AcceptedPaymentMethods;
use Square\Models\CheckoutOptions;
use Square\Models\CreatePaymentLinkRequest;
use Square\Models\Money;
use Square\Models\Order;
use Square\Models\OrderLineItem;
use Square\Models\OrderPricingOptions;
use Square\Models\PrePopulatedData;
use Square\SquareClient;

class SquareCheckout
{
    public static function redirectToSquare(
        int $amount,
        Payment $payment,
        User $user,
        string $name,
        string $variation_name
    ) {
        $basePrice = new Money();
        $basePrice->setAmount($amount);
        $basePrice->setCurrency('USD');

        $orderLineItem = new OrderLineItem('1');
        $orderLineItem->setName($name);
        $orderLineItem->setVariationName($variation_name);
        $orderLineItem->setBasePriceMoney($basePrice);

        $pricingOptions = new OrderPricingOptions();
        $pricingOptions->setAutoApplyDiscounts(false);
        $pricingOptions->setAutoApplyTaxes(false);

        $order = new Order(config('square.location_id'));
        $order->setReferenceId((string) $payment->id);
        $order->setLineItems([$orderLineItem]);
        $order->setPricingOptions($pricingOptions);

        $acceptedPaymentMethods = new AcceptedPaymentMethods();
        $acceptedPaymentMethods->setApplePay(true);
        $acceptedPaymentMethods->setGooglePay(true);
        $acceptedPaymentMethods->setCashAppPay(true);
        $acceptedPaymentMethods->setAfterpayClearpay(false);

        $checkoutOptions = new CheckoutOptions();
        $checkoutOptions->setAllowTipping(false);
        $checkoutOptions->setRedirectUrl(route('pay.complete'));
        $checkoutOptions->setMerchantSupportEmail(config('services.treasurer_email'));
        $checkoutOptions->setAskForShippingAddress(false);
        $checkoutOptions->setAcceptedPaymentMethods($acceptedPaymentMethods);
        $checkoutOptions->setEnableCoupon(false);
        $checkoutOptions->setEnableLoyalty(false);

        $prePopulatedData = new PrePopulatedData();
        $prePopulatedData->setBuyerEmail($user->gt_email);

        if ($user->phone !== null) {
            if (Str::startsWith('+', $user->phone)) {
                // already has country code prefix
                $prePopulatedData->setBuyerPhoneNumber($user->phone);
            } elseif (Str::startsWith('1', $user->phone) && Str::length($user->phone) === 11) {
                // has united states country code but no +, just add it
                $prePopulatedData->setBuyerPhoneNumber('+'.$user->phone);
            } elseif (Str::length($user->phone) === 10) {
                // assume united states number
                $prePopulatedData->setBuyerPhoneNumber('+1'.$user->phone);
            } else {
                // assume international number with country code
                $prePopulatedData->setBuyerPhoneNumber('+'.$user->phone);
            }
        }

        $paymentLinkRequest = new CreatePaymentLinkRequest();
        $paymentLinkRequest->setIdempotencyKey($payment->unique_id);
        $paymentLinkRequest->setOrder($order);
        $paymentLinkRequest->setCheckoutOptions($checkoutOptions);
        $paymentLinkRequest->setPrePopulatedData($prePopulatedData);

        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $checkoutApi = $square->getCheckoutApi();

        $parentSpan = SentrySdk::getCurrentHub()->getSpan();

        if ($parentSpan !== null) {
            $context = new SpanContext();
            $context->setOp('square.create_payment_link');
            $span = $parentSpan->startChild($context);
            SentrySdk::getCurrentHub()->setSpan($span);
        }

        $paymentLinkResponse = $checkoutApi->createPaymentLink($paymentLinkRequest);

        if ($parentSpan !== null) {
            // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
            $span->finish();
            SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if (! $paymentLinkResponse->isSuccess()) {
            Log::error(self::class.': Error creating payment link - '.json_encode($paymentLinkResponse->getErrors()));
            // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
            \Sentry\captureMessage(json_encode($paymentLinkResponse->getErrors()));

            return view(
                'square.error',
                [
                    'message' => 'We could not contact Square to begin your payment. Post in #it-helpdesk '
                        .'with the trace ID shown at the bottom of this page.',
                ]
            );
        }

        $paymentLink = $paymentLinkResponse->getResult()->getPaymentLink();

        $payment->order_id = $paymentLinkResponse->getResult()->getPaymentLink()->getOrderId();
        $payment->checkout_id = $paymentLink->getId();
        $payment->url = $paymentLink->getLongUrl();
        $payment->save();

        return redirect($payment->url);
    }
}
