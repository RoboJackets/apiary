<?php

declare(strict_types=1);

// phpcs:disable Generic.Formatting.SpaceBeforeCast.NoSpace
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed
// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Http\Controllers;

use App\Http\Requests\SquareCompleteRequest;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
use Square\Models\OrderServiceCharge;
use Square\Models\OrderServiceChargeCalculationPhase;
use Square\Models\OrderState;
use Square\Models\PrePopulatedData;
use Square\SquareClient;

class SquareCheckoutController extends Controller
{
    public function payDues(Request $request)
    {
        $user = $request->user();

        if (! $user->signed_latest_agreement) {
            return view('dues.agreementrequired');
        }

        if ($user->is_active) {
            return view('dues.alreadypaid');
        }

        $transactionWithNoPayment = DuesTransaction::doesntHave('payment')
            ->current()
            ->whereHas('package', static function (Builder $query) use ($user): void {
                $query->userCanPurchase($user);
            })
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        $transactionWithIncompletePayment = DuesTransaction::where('user_id', $user->id)
            ->current()
            ->whereHas('package', static function (Builder $query) use ($user): void {
                $query->userCanPurchase($user);
            })
            ->whereHas('payment', static function (Builder $q): void {
                $q->where('amount', 0);
                $q->where('method', 'square');
            })
            ->latest('updated_at')
            ->first();

        if (null !== $transactionWithIncompletePayment) {
            $transaction = $transactionWithIncompletePayment;

            $payment = $transaction->payment[0];
        } elseif (null !== $transactionWithNoPayment) {
            $transaction = $transactionWithNoPayment;

            $payment = new Payment();
            // @phan-suppress-next-line PhanTypeMismatchPropertyProbablyReal
            $payment->amount = 0.00;
            $payment->method = 'square';
            $payment->recorded_by = $user->id;
            $payment->unique_id = Payment::generateUniqueId();
            $payment->notes = 'Checkout flow started';

            $transaction->payment()->save($payment);
        } else {
            return view(
                'square.error',
                [
                    'message' => 'We could not find a transaction ready for payment.',
                ]
            );
        }

        $amount = (int) ($transaction->package->cost * 100);

        return self::redirect($amount, $payment, $user, 'Dues', $transaction->package->name);
    }

    public function payTravel(Request $request)
    {
        $user = $request->user();

        $assignment = $user->current_travel_assignment;

        if (null === $assignment) {
            return view('travel.noassignment');
        }

        if ($assignment->is_paid) {
            $any_assignment_needs_payment = $user->assignments()
                ->unpaid()
                ->oldest('travel.departure_date')
                ->oldest('travel.return_date')
                ->first();

            if (null === $any_assignment_needs_payment) {
                return view('travel.alreadypaid');
            }

            $assignment = $any_assignment_needs_payment;
        }

        if (! $user->is_active) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'pay dues',
                ]
            );
        }

        if (! $user->signed_latest_agreement) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'sign the latest membership agreement',
                ]
            );
        }

        if (0 === $assignment->payment()->count()) {
            $payment = new Payment();
            // @phan-suppress-next-line PhanTypeMismatchPropertyProbablyReal
            $payment->amount = 0;
            $payment->method = 'square';
            $payment->recorded_by = $user->id;
            $payment->unique_id = Payment::generateUniqueId();
            $payment->notes = 'Checkout flow started';

            $assignment->payment()->save($payment);
        } else {
            $payment = $assignment->payment()->sole();
        }

        $amount = $assignment->travel->fee_amount * 100;

        if (null !== $payment->url) {
            return redirect($payment->url);
        }

        return self::redirect($amount, $payment, $user, 'Travel Fee', $assignment->travel->name);
    }

    private static function redirect(int $amount, Payment $payment, User $user, string $name, string $variation_name)
    {
        $basePrice = new Money();
        $basePrice->setAmount($amount);
        $basePrice->setCurrency('USD');

        $surcharge = new Money();
        $surcharge->setAmount(Payment::calculateSurcharge($amount));
        $surcharge->setCurrency('USD');

        $orderLineItem = new OrderLineItem('1');
        $orderLineItem->setName($name);
        $orderLineItem->setVariationName($variation_name);
        $orderLineItem->setBasePriceMoney($basePrice);

        $orderServiceCharge = new OrderServiceCharge();
        $orderServiceCharge->setName('Card Processing Surcharge');
        $orderServiceCharge->setAmountMoney($surcharge);
        $orderServiceCharge->setCalculationPhase(OrderServiceChargeCalculationPhase::TOTAL_PHASE);

        $order = new Order(config('square.location_id'));
        $order->setReferenceId((string) $payment->id);
        $order->setLineItems([$orderLineItem]);
        $order->setServiceCharges([$orderServiceCharge]);

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

        $prePopulatedData = new PrePopulatedData();
        $prePopulatedData->setBuyerEmail($user->gt_email);

        if (null !== $user->phone) {
            if (Str::startsWith('+', $user->phone)) {
                // already has country code prefix
                $prePopulatedData->setBuyerPhoneNumber($user->phone);
            } elseif (Str::startsWith('1', $user->phone) && 11 === Str::length($user->phone)) {
                // has united states country code but no +, just add it
                $prePopulatedData->setBuyerPhoneNumber('+'.$user->phone);
            } else {
                // assume united states number
                $prePopulatedData->setBuyerPhoneNumber('+1'.$user->phone);
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

        if (null !== $parentSpan) {
            $context = new SpanContext();
            $context->setOp('square.create_payment_link');
            $span = $parentSpan->startChild($context);
            SentrySdk::getCurrentHub()->setSpan($span);
        }

        $paymentLinkResponse = $checkoutApi->createPaymentLink($paymentLinkRequest);

        if (null !== $parentSpan) {
            // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
            $span->finish();
            SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if (! $paymentLinkResponse->isSuccess()) {
            Log::error(self::class.' Error creating payment link - '.json_encode($paymentLinkResponse->getErrors()));
            // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
            \Sentry\captureMessage(json_encode($paymentLinkResponse->getErrors()));

            return view(
                'square.error',
                [
                    'message' => 'We could not contact Square to begin your payment. Please post in #it-helpdesk '
                        .'with the trace ID shown at the bottom of this page.',
                ]
            );
        }

        $paymentLink = $paymentLinkResponse->getResult()->getPaymentLink();

        $payment->order_id = $paymentLinkResponse->getResult()->getPaymentLink()->getOrderId();
        $payment->checkout_id = $paymentLink->getId();
        $payment->url = $paymentLink->getUrl();
        $payment->save();

        return redirect($payment->url);
    }

    public function complete(SquareCompleteRequest $request)
    {
        $payment = Payment::where('order_id', $request->input('transactionId'))->firstOrFail();

        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $ordersApi = $square->getOrdersApi();

        $parentSpan = SentrySdk::getCurrentHub()->getSpan();

        if (null !== $parentSpan) {
            $context = new SpanContext();
            $context->setOp('square.retrieve_order');
            $span = $parentSpan->startChild($context);
            SentrySdk::getCurrentHub()->setSpan($span);
        }

        $retrieveOrderResponse = $ordersApi->retrieveOrder($payment->order_id);

        if (null !== $parentSpan) {
            // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
            $span->finish();
            SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if (! $retrieveOrderResponse->isSuccess()) {
            Log::error(self::class.' Error retrieving order - '.json_encode($retrieveOrderResponse->getErrors()));

            return view(
                'square.error',
                [
                    'message' => 'We could not contact Square to finalize your payment.',
                ]
            );
        }

        $order = $retrieveOrderResponse->getResult()->getOrder();

        switch ($order->getState()) {
            case OrderState::COMPLETED:
                $tender = $order->getTenders()[0];
                $payment->amount = $tender->getAmountMoney()->getAmount() / 100;

                $processingFeeMoney = $tender->getProcessingFeeMoney();
                if (null !== $processingFeeMoney) {
                    $payment->processing_fee = $processingFeeMoney->getAmount() / 100;
                }

                $cardDetails = $tender->getCardDetails();
                if (null !== $cardDetails) {
                    $card = $cardDetails->getCard();
                    if (null !== $card) {
                        $payment->card_brand = $card->getCardBrand();
                        $payment->card_type = $card->getCardType();
                        $payment->last_4 = $card->getLast4();
                        $payment->prepaid_type = $card->getPrepaidType();
                    }
                    $payment->entry_method = $cardDetails->getEntryMethod();
                }
                $payment->notes = 'Checkout flow completed';
                $payment->save();

                alert()->success("We've received your payment", 'Success!');

                return redirect('/');
            case OrderState::CANCELED:
                return view(
                    'square.error',
                    [
                        'message' => 'Your order was canceled.',
                    ]
                );
            case OrderState::OPEN:
                return view('square.processing');
            default:
                throw new Exception('Unexpected order state');
        }
    }
}
