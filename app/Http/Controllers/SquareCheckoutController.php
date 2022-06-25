<?php

declare(strict_types=1);

// phpcs:disable Generic.Formatting.SpaceBeforeCast.NoSpace

namespace App\Http\Controllers;

use App\Http\Requests\SquareCompleteRequest;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use Exception;
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

        $orderRequest = new CreateOrderRequest();
        $orderRequest->setOrder($order);
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
            default:
                throw new Exception('Unexpected order state');
        }
    }
}
