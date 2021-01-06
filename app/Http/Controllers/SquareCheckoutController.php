<?php

declare(strict_types=1);

// phpcs:disable Generic.Formatting.SpaceBeforeCast.NoSpace

namespace App\Http\Controllers;

use App\Http\Requests\SquareCompleteRequest;
use App\Models\DuesTransaction;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Square\Models\CreateCheckoutRequest;
use Square\Models\CreateOrderRequest;
use Square\Models\Money;
use Square\Models\Order;
use Square\Models\OrderLineItem;
use Square\Models\OrderServiceCharge;
use Square\Models\OrderServiceChargeCalculationPhase;
use Square\Models\OrderState;
use Square\SquareClient;

class SquareCheckoutController extends Controller
{
    private const PER_TRANSACTION_FEE = 30;  // cents
    private const PERCENTAGE_FEE = 2.9;

    public function payDues(Request $request)
    {
        $user = $request->user();

        if (! $user->hasSignedLatestAgreement()) {
            return view('dues.agreementrequired');
        }

        $transactionWithNoPayment = DuesTransaction::doesntHave('payment')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        $transactionWithIncompletePayment = DuesTransaction::where('user_id', $user->id)
            ->current()
            ->whereHas('payment', static function (Builder $q): void {
                $q->where('amount', 0.00);
                $q->where('method', 'square');
            })->first();

        if (null !== $transactionWithIncompletePayment) {
            $transaction = $transactionWithIncompletePayment;

            $payment = $transaction->payment[0];
        } elseif (null !== $transactionWithNoPayment) {
            $transaction = $transactionWithNoPayment;

            $payment = new Payment();
            $payment->amount = 0.00;
            $payment->method = 'square';
            $payment->recorded_by = $user->id;
            $payment->unique_id = self::generateUniqueId();
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

        $basePrice = new Money();
        $basePrice->setAmount($amount);
        $basePrice->setCurrency('USD');

        $surcharge = new Money();
        $surcharge->setAmount(self::calculateSurcharge($amount));
        $surcharge->setCurrency('USD');

        $orderLineItem = new OrderLineItem('1');
        $orderLineItem->setName('Dues');
        $orderLineItem->setVariationName($transaction->package->name);
        $orderLineItem->setBasePriceMoney($basePrice);

        $orderServiceCharge = new OrderServiceCharge();
        $orderServiceCharge->setName('Card Processing Surcharge');
        $orderServiceCharge->setAmountMoney($surcharge);
        $orderServiceCharge->setCalculationPhase(OrderServiceChargeCalculationPhase::TOTAL_PHASE);

        $order = new Order(config('square.location_id'));
        $order->setReferenceId((string) $payment->id);
        $order->setLineItems([$orderLineItem]);
        $order->setServiceCharges([$orderServiceCharge]);

        $orderRequest = new CreateOrderRequest();
        $orderRequest->setOrder($order);
        $orderRequest->setLocationId(config('square.location_id'));
        $orderRequest->setIdempotencyKey($payment->unique_id);

        $checkoutRequest = new CreateCheckoutRequest($payment->unique_id, $orderRequest);
        $checkoutRequest->setMerchantSupportEmail('treasurer@robojackets.org');
        $checkoutRequest->setPrePopulateBuyerEmail($user->gt_email);
        $checkoutRequest->setRedirectUrl(route('pay.complete'));

        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $checkoutApi = $square->getCheckoutApi();
        $checkoutResponse = $checkoutApi->createCheckout(config('square.location_id'), $checkoutRequest);

        if (! $checkoutResponse->isSuccess()) {
            Log::error(self::class.' Error creating checkout - '.json_encode($checkoutResponse->getErrors()));

            return view(
                'square.error',
                [
                    'message' => 'We could not contact Square to begin your payment.',
                ]
            );
        }

        $checkout = $checkoutResponse->getResult()->getCheckout();

        $payment->checkout_id = $checkout->getId();
        $payment->order_id = $checkout->getOrder()->getId();
        $payment->save();

        return redirect($checkout->getCheckoutPageUrl());
    }

    public function complete(SquareCompleteRequest $request)
    {
        $payment = Payment::where('id', $request->input('referenceId'))->firstOrFail();

        if ($payment->checkout_id !== $request->input('checkoutId') || null === $payment->order_id) {
            return view(
                'square.error',
                [
                    'message' => 'We could not match your payment in our database.',
                ]
            );
        }

        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $ordersApi = $square->getOrdersApi();

        $retrieveOrderResponse = $ordersApi->retrieveOrder($payment->order_id);

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
        }
    }

    private static function generateUniqueId(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    private static function calculateSurcharge(int $amount): int
    {
        return (int) round(
            (($amount + self::PER_TRANSACTION_FEE) / ((100 - self::PERCENTAGE_FEE) / 100)) - $amount,
            0,
            PHP_ROUND_HALF_UP
        );
    }
}
