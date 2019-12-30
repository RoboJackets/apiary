<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DuesTransaction;
use App\Events\PaymentSuccess;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Notifications\Payment\ConfirmationNotification as Confirm;
use App\Payment;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SquareConnect\Api\CheckoutApi;
use SquareConnect\Api\TransactionsApi;
use SquareConnect\Configuration;
use SquareConnect\Model\CreateCheckoutRequest;
use SquareConnect\Model\CreateOrderRequest;
use Throwable;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-payments', ['only' => ['index']]);
        $this->middleware('permission:create-payments|create-payments-own', ['only' => ['store']]);
        $this->middleware('permission:read-payments|read-payments-own', ['only' => ['show']]);
        $this->middleware('permission:update-payments', ['only' => ['update']]);
        $this->middleware('permission:delete-payments', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $payments = Payment::all();

        return response()->json(['status' => 'success', 'payments' => $payments]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        if ($request->user()->cant('create-payments-'.$request->input('method'))) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Forbidden - you do not have permission to accept that payment method',
                ],
                403
            );
        }

        try {
            $payment = Payment::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        $dbPayment = Payment::findOrFail($payment->id);
        $dbPayment->payable->user->notify(new Confirm($dbPayment));
        event(new PaymentSuccess($dbPayment));

        return response()->json(['status' => 'success', 'payment' => $dbPayment], 201);
    }

    /**
     * Handles payment request from user-facing UI.
     */
    public function storeUser(Request $request)
    {
        $user = $request->user();

        //Find the most recent DuesTransaction without a payment attempt
        $transactWithoutPmt = DuesTransaction::doesntHave('payment')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        //Find Dues Transactions with failed/canceled/abandoned ($0) payment attempts
        // and that have not passed the effective end
        $transactZeroPmt = DuesTransaction::where('user_id', $user->id)
            ->whereHas('package', static function (Eloquent $q): void {
                $q->whereDate('effective_end', '>=', date('Y-m-d'));
            })->whereHas('payment', static function (Eloquent $q): void {
                $q->where('amount', 0.00);
                $q->where('method', 'square');
            })->first();

        if (null !== $transactZeroPmt) {
            $payable = $transactZeroPmt;
        } elseif (null !== $transactWithoutPmt) {
            $payable = $transactWithoutPmt;
        } else {
            //No transactions found without payment
            Log::warning(self::class.': No eligible Dues Transaction found for payment.');

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 400,
                        'error_message' => 'No eligible Dues Transaction found for payment.',
                    ]
                ),
                400
            );
        }

        $amount = $payable->package->cost;
        $name = 'Dues - '.$payable->package->name;
        $email = $user->gt_email;

        if (null !== $transactZeroPmt) {
            $payment = $transactZeroPmt->payment[0];
            $payment->unique_id = bin2hex(openssl_random_pseudo_bytes(10));
            $payment->save();
        } else {
            $payment = new Payment();
            $payment->amount = 0.00;
            $payment->method = 'square';
            $payment->recorded_by = $user->id;
            $payment->unique_id = bin2hex(openssl_random_pseudo_bytes(10));
            $payment->notes = 'Pending Square Payment';
            $payable->payment()->save($payment);
        }

        $squareResult = $this->createSquareCheckout($name, intval($amount), $email, $payment, true);
        if (is_a($squareResult, RedirectResponse::class)) {
            return $squareResult;
        }

        Log::error(self::class.' - Error Creating Square Checkout - '.$squareResult);

        return response(
            view(
                'errors.generic',
                [
                    'error_code' => 500,
                    'error_message' => 'Unable to process Square Checkout request.',
                ]
            ),
            500
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $payment = Payment::find($id);
        if (null !== $payment) {
            return response()->json(['status' => 'success', 'payment' => $payment]);
        }

        return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, int $id): JsonResponse
    {
        $payment = Payment::find($id);
        if (null === $payment) {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }

        $payment->update($request->all());

        $payment = Payment::find($payment->id);
        if (null !== $payment) {
            return response()->json(['status' => 'success', 'payment' => $payment]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $payment = Payment::find($id);
        if (true === $payment->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Payment deleted.']);
        }

        return response()->json(['status' => 'error',
            'message' => 'Payment does not exist or was previously deleted.',
        ], 422);
    }

    /**
     * Creates Square Checkout Payment Flow.
     *
     * @param string $name Name of line item
     * @param int $amount Amount in *whole* dollars to be paid (excluding fees!)
     * @param string $email Email address for Square Receipt
     * @param \App\Payment $payment Payment Model
     * @param bool $addFee Adds $3.00 transaction fee if true
     */
    public function createSquareCheckout(string $name, int $amount, string $email, Payment $payment, bool $addFee)
    {
        $api = new CheckoutApi();
        $location = config('payment.square.location_id');
        $token = config('payment.square.token');

        $line_items = [
            [
                'name' => $name ?? 'Miscellaneous Payment',
                'quantity' => '1',
                'base_price_money' => [
                    'amount' => $amount * 100,
                    'currency' => 'USD',
                ],
            ],
        ];

        if ($addFee) {
            $line_items[] = [
                'name' => 'Transaction Fee',
                'quantity' => '1',
                'base_price_money' => [
                    'amount' => 300,
                    'currency' => 'USD',
                ],
            ];
        }

        $order = new CreateOrderRequest([
            'reference_id' => 'PMT'.$payment->id,
            'line_items' => $line_items,
        ]);

        $checkout_request = new CreateCheckoutRequest([
            'idempotency_key' => $payment->unique_id,
            'order' => $order,
            'merchant_support_email' => 'treasurer@robojackets.org',
            'pre_populate_buyer_email' => $email,
            'redirect_url' => route('payments.complete'),
        ]);

        Configuration::getDefaultConfiguration()->setAccessToken($token);
        $checkout = $api->createCheckout($location, $checkout_request);

        $payment->checkout_id = $checkout['checkout']['id'];
        $payment->save();

        return redirect($checkout['checkout']['checkout_page_url']);
    }

    /**
     * Processes Square redirect after completed checkout transaction.
     */
    public function handleSquareResponse(Request $request)
    {
        //Make sure we have all of the necessary parameters from Square
        $validator = Validator::make($request->all(), [
            'checkoutId' => 'required',
            'transactionId' => 'required',
            'referenceId' => 'required',
        ]);

        //If we don't, something fishy is going on.
        if ($validator->fails()) {
            Log::warning(self::class.' - Missing parameter in Square response');

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 400,
                        'error_message' => 'Missing parameter in Square response.',
                    ]
                ),
                400
            );
        }

        $checkout_id = $request->input('checkoutId');
        $server_txn_id = $request->input('transactionId');
        $client_txn_id = $request->input('referenceId');

        //Check to make sure the reference ID is "PMTXXXX"
        $payment_id = substr($client_txn_id, 3);
        Log::debug(self::class.' - Stripping Reference ID '.$client_txn_id.' to '.$payment_id);
        if (! is_numeric($payment_id) || 'PMT' !== substr($client_txn_id, 0, 3)) {
            Log::error(self::class.' - Invalid Payment ID in Square response '.$payment_id);

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 422,
                        'error_message' => 'Invalid Payment ID in Square response.',
                    ]
                ),
                422
            );
        }

        //Find the payment
        $payment = Payment::find($payment_id);
        if (null === $payment) {
            Log::warning(self::class.' - Error locating Payment '.$payment_id);

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 404,
                        'error_message' => 'Unable to locate payment.',
                    ]
                ),
                404
            );
        }
        Log::debug(self::class.' - Found Payment '.$payment_id);

        //Check if the payment has already been processed
        if (0 !== intval($payment->amount)) {
            Log::warning(self::class.' - Payment Already Processed '.$payment_id);

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 409,
                        'error_message' => 'Payment already processed.',
                    ]
                ),
                409
            );
        }

        //Prepare Square API Call
        $txnClient = new TransactionsApi();
        $location = config('payment.square.location_id');
        $token = config('payment.square.token');
        Configuration::getDefaultConfiguration()->setAccessToken($token);

        $square_txn = null;

        //Query Square API to get authoritative data
        //See #284 for reasoning for loop
        $counter = 0;
        while ($counter < 10) {
            $square_txn = $this->getSquareTransaction($txnClient, $location, $server_txn_id);
            if (! $square_txn instanceof Throwable) {
                break;
            }
            $counter++;
            usleep($counter * 100000);
        }

        if ($square_txn instanceof Throwable) {
            Bugsnag::notifyException($square_txn);

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 500,
                        'error_message' => 'Error querying Square transaction',
                    ]
                ),
                500
            );
        }

        // @phan-suppress-next-line PhanPossiblyNonClassMethodCall,PhanPossiblyUndeclaredMethod
        $tenders = $square_txn->getTransaction()->getTenders();
        $amount = $tenders[0]->getAmountMoney()->getAmount() / 100;
        $proc_fee = $tenders[0]->getProcessingFeeMoney()->getAmount() / 100;
        // @phan-suppress-next-line PhanPossiblyNonClassMethodCall,PhanPossiblyUndeclaredMethod
        $created_at = $square_txn->getTransaction()->getCreatedAt();
        Log::debug(
            self::class.' - Square Transaction Details for '.$server_txn_id,
            ['Amount' => $amount, 'Txn Date' => $created_at, 'Processing Fee' => $proc_fee]
        );

        //Compare received payment amount to expected payment amount
        $payable = $payment->payable;
        $expected_amount = $payable->getPayableAmount();
        $difference = $amount - $expected_amount;
        if (3 !== intval($difference)) {
            $message = 'Payment Discrepancy Found for ID '.$payment->id;
            $data = ['Expected' => $expected_amount, 'Actual' => $amount, 'Server Txn ID' => $server_txn_id];
            Log::error(self::class.' - '.$message, $data);

            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 409,
                        'error_message' => 'Payment discrepancy found. Please contact the Treasurer for assistance.',
                    ]
                ),
                409
            );
        }

        $payment->amount = $amount;
        $payment->processing_fee = $proc_fee;
        $payment->checkout_id = $checkout_id;
        $payment->server_txn_id = $server_txn_id;
        $payment->client_txn_id = $client_txn_id;
        $payment->notes = '';
        $payment->save();

        //Notify user of successful payment
        $payment->payable->user->notify(new Confirm($payment));

        Log::debug(self::class.'Payment '.$payment->id.' Updated Successfully');

        alert()->success("We've received your payment", 'Success!');

        event(new PaymentSuccess($payment));

        return redirect('/');
    }

    /**
     * Queries Square Transaction API for transaction details.
     *
     * @return \Throwable|\SquareConnect\Model\RetrieveTransactionResponse
     */
    protected function getSquareTransaction(TransactionsApi $client, string $location, string $server_txn_id)
    {
        try {
            Log::debug(self::class.' - Querying Square for Transaction '.$server_txn_id);
            $square_txn = $client->retrieveTransaction($location, $server_txn_id);
        } catch (\Throwable $e) {
            Log::debug(self::class.' - Error querying Square transaction', [$e->getMessage()]);

            return $e;
        }
        Log::debug(self::class.' - Retrieved Square Transaction '.$server_txn_id);

        return $square_txn;
    }
}
