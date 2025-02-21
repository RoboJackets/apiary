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
use Square\Checkout\PaymentLinks\Requests\CreatePaymentLinkRequest;
use Square\SquareClient;
use Square\Types\AcceptedPaymentMethods;
use Square\Types\CheckoutOptions;
use Square\Types\Money;
use Square\Types\Order;
use Square\Types\OrderLineItem;
use Square\Types\OrderPricingOptions;
use Square\Types\PrePopulatedData;

class SquareCheckout
{
    public static function redirectToSquare(
        int $amount,
        Payment $payment,
        User $user,
        string $name,
        string $variation_name
    ) {
        $phoneNumber = null;

        if ($user->phone !== null) {
            if (Str::startsWith('+', $user->phone)) {
                // already has country code prefix
                $phoneNumber = $user->phone;
            } elseif (Str::startsWith('1', $user->phone) && Str::length($user->phone) === 11) {
                // has united states country code but no +, just add it
                $phoneNumber = '+'.$user->phone;
            } elseif (Str::length($user->phone) === 10) {
                // assume united states number
                $phoneNumber = '+1'.$user->phone;
            } else {
                // assume international number with country code
                $phoneNumber = '+'.$user->phone;
            }
        }

        $paymentLinkRequest = new CreatePaymentLinkRequest([
            'idempotencyKey' => $payment->unique_id,
            'order' => new Order([
                'locationId' => config('square.location_id'),
                'referenceId' => $payment->id,
                'lineItems' => [
                    new OrderLineItem([
                        'quantity' => 1,
                        'name' => $name,
                        'variationName' => $variation_name,
                        'basePriceMoney' => new Money([
                            'amount' => $amount,
                            'currency' => 'USD',
                        ]),
                    ]),
                ],
                'pricingOptions' => new OrderPricingOptions([
                    'autoApplyDiscounts' => false,
                    'autoApplyTaxes' => false,
                ]),
            ]),
            'checkoutOptions' => new CheckoutOptions([
                'allowTipping' => false,
                'redirectUrl' => route('pay.complete'),
                'merchantSupportEmail' => 'payments@robojackets.org',
                'askForShippingAddress' => false,
                'acceptedPaymentMethods' => new AcceptedPaymentMethods([
                    'applePay' => true,
                    'googlePay' => true,
                    'cashAppPay' => true,
                    'afterpayClearpay' => false,
                ]),
                'enableCoupon' => false,
                'enableLoyalty' => false,
            ]),
            'prePopulatedData' => new PrePopulatedData([
                'buyerEmail' => $user->gt_email,
                'buyerPhoneNumber' => $phoneNumber,
            ]),
        ]);

        $square = new SquareClient(
            token: config('square.access_token'),
            options: [
                'baseUrl' => config('square.base_url'),
            ]
        );

        $parentSpan = SentrySdk::getCurrentHub()->getSpan();

        if ($parentSpan !== null) {
            $context = new SpanContext();
            $context->setOp('square.create_payment_link');
            $span = $parentSpan->startChild($context);
            SentrySdk::getCurrentHub()->setSpan($span);
        }

        $paymentLinkResponse = $square->checkout->paymentLinks->create($paymentLinkRequest);

        if ($parentSpan !== null) {
            // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
            $span->finish();
            SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if ($paymentLinkResponse === null) {
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

        $paymentLink = $paymentLinkResponse->getPaymentLink();

        $payment->order_id = $paymentLink->getOrderId();
        $payment->checkout_id = $paymentLink->getId();
        $payment->url = $paymentLink->getLongUrl();
        $payment->save();

        return redirect($payment->url);
    }
}
