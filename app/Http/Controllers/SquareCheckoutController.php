<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments

namespace App\Http\Controllers;

use App\Http\Requests\SquareCompleteRequest;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Util\SquareCheckout;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
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

        return Cache::lock(name: $user->uid.'_payment', seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($user) {
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

                if ($transactionWithIncompletePayment !== null) {
                    $transaction = $transactionWithIncompletePayment;

                    $payment = $transaction->payment[0];
                } elseif ($transactionWithNoPayment !== null) {
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

                if ($payment->url !== null) {
                    return redirect($payment->url);
                }

                return SquareCheckout::redirectToSquare($amount, $payment, $user, 'Dues', $transaction->package->name);
            }
        );
    }

    public function payTravel(Request $request)
    {
        $user = $request->user();

        $assignment = $user->current_travel_assignment;

        if ($assignment === null) {
            return view('travel.noassignment');
        }

        return Cache::lock(name: $user->uid.'_payment', seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($user, $assignment) {
                if ($assignment->is_paid) {
                    $any_assignment_needs_payment = $user->assignments()
                        ->whereHas(
                            'travel',
                            static function (Builder $query): void {
                                $query->whereIn('status', ['approved', 'complete']);
                            }
                        )
                        ->unpaid()
                        ->oldest('travel.departure_date')
                        ->oldest('travel.return_date')
                        ->first();

                    if ($any_assignment_needs_payment === null) {
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

                if ($assignment->payment()->count() === 0) {
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

                if ($payment->url !== null) {
                    return redirect($payment->url);
                }

                return SquareCheckout::redirectToSquare(
                    $amount,
                    $payment,
                    $user,
                    'Trip Fee',
                    $assignment->travel->name
                );
            }
        );
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

        if ($parentSpan !== null) {
            $context = new SpanContext();
            $context->setOp('square.retrieve_order');
            $span = $parentSpan->startChild($context);
            SentrySdk::getCurrentHub()->setSpan($span);
        }

        $retrieveOrderResponse = $ordersApi->retrieveOrder($payment->order_id);

        if ($parentSpan !== null) {
            // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
            $span->finish();
            SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if (! $retrieveOrderResponse->isSuccess()) {
            Log::error(self::class.' Error retrieving order - '.json_encode($retrieveOrderResponse->getErrors()));
            // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
            \Sentry\captureMessage(json_encode($retrieveOrderResponse->getErrors()));

            return view(
                'square.error',
                [
                    'message' => 'We could not contact Square to finalize your payment.',
                ]
            );
        }

        $order = $retrieveOrderResponse->getResult()->getOrder();

        if ($order->getTenders() !== null && count($order->getTenders()) > 0) {
            $tender = $order->getTenders()[0];
            $payment->amount = $tender->getAmountMoney()->getAmount() / 100;

            $processingFeeMoney = $tender->getProcessingFeeMoney();
            if ($processingFeeMoney !== null) {
                $payment->processing_fee = $processingFeeMoney->getAmount() / 100;
            }

            $cardDetails = $tender->getCardDetails();
            if ($cardDetails !== null) {
                $card = $cardDetails->getCard();
                if ($card !== null) {
                    $payment->card_brand = $card->getCardBrand();
                    $payment->card_type = $card->getCardType();
                    $payment->last_4 = $card->getLast4();
                    $payment->prepaid_type = $card->getPrepaidType();
                }
                $payment->entry_method = $cardDetails->getEntryMethod();
            }
            $payment->notes = 'Checkout flow completed';
            $payment->save();

            alert()->success('Success!', 'We processed your payment!');

            return redirect('/');
        }

        throw new Exception('Unexpected order state');
    }
}
