<?php

namespace App\Http\Controllers;

use Log;
use Bugsnag;
use App\Event;
use Validator;
use App\Payment;
use App\DuesTransaction;
use Illuminate\Http\Request;
use SquareConnect\ApiException;
use SquareConnect\Configuration;
use SquareConnect\Api\CheckoutApi;
use SquareConnect\Model\Transaction;
use SquareConnect\Api\TransactionsApi;
use SquareConnect\Model\CreateOrderRequest;
use SquareConnect\Model\CreateCheckoutRequest;
use App\Notifications\Payment\ConfirmationNotification as Confirm;

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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = Payment::all();

        return response()->json(['status' => 'success', 'payments' => $payments]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        if (! $request->has('recorded_by') ||
            $currentUser->cant('update-payments')) {
            $request['recorded_by'] = $currentUser->id;
        }

        $this->validate($request, [
            'amount' => 'required|numeric',
            'method' => 'required|string',
            'recorded_by' => 'numeric|exists:users,id',
            'payable_type' => 'required|string',
            'payable_id' => 'required|numeric',
        ]);

        try {
            $payment = Payment::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($payment->id)) {
            $dbPayment = Payment::findOrFail($payment->id);
            $dbPayment->payable->user->notify(new Confirm($dbPayment));

            return response()->json(['status' => 'success', 'payment' => $dbPayment], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Handles payment request from user-facing UI.
     *
     * @param Request $request
     * @return mixed
     */
    public function storeUser(Request $request)
    {
        $user = auth()->user();
        $payable = null;
        $payable_type = null;
        $payable_id = null;
        $name = null;
        $amount = null;
        $email = $user->gt_email;

        if ($request->method() == 'POST') {
            $this->validate($request, [
                'payable_type' => 'required|string',
                'payable_id' => 'required|numeric',
            ]);
            $payable_type = $request->input('payable_type');
            $payable_id = $request->input('payable_id');
        } else {
            //Assuming DuesTransaction for now

            //Find DuesTransactions without payment attempts
            $transactWithoutPmt = DuesTransaction::doesntHave('payment')
                ->where('user_id', $user->id)->first();

            //Find Dues Transactions with failed/canceled/abandoned ($0) payment attempts
            // and that have not passed the effective end
            $transactZeroPmt = DuesTransaction::where('user_id', $user->id)
                ->whereHas('package', function ($q) {
                    $q->whereDate('effective_end', '>=', date('Y-m-d'));
                })
                ->whereHas('payment', function ($q) {
                    $q->where('amount', 0.00);
                    $q->where('method', 'square');
                })->first();

            if ($transactZeroPmt) {
                $payable = $transactZeroPmt;
            } elseif ($transactWithoutPmt) {
                $payable = $transactWithoutPmt;
            } else {
                //No transactions found without payment
                Log::warning(get_class().': No eligible Dues Transaction found for payment.');

                return response(view('errors.generic',
                    ['error_code' => 400,
                        'error_message' => 'No eligible Dues Transaction found for payment.', ]), 400);
            }

            $amount = $payable->package->cost;
            $name = 'Dues - '.$payable->package->name;
            $email = $user->gt_email;
        }

        if (! $payable) {
            if ($payable_type == "App\DuesTransaction") {
                $payable = DuesTransaction::find($payable_id);
                $amount = $payable->package->amount;
                $name = 'Dues - '.$payable->package->name;
                $email = $user->gt_email;
            } elseif ($payable_type == "App\Event") {
                $payable = Event::find($payable_id);
                $amount = $payable->price;
                $name = 'Event - '.$payable->name;
                $email = $user->gt_email;
            } else {
                return response()->json(['status' => 'error', 'error' => 'Invalid Payable Type'], 400);
            }
        }

        if ($transactZeroPmt) {
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

        $squareResult = $this->createSquareCheckout($name, $amount, $email, $payment, true);
        if (is_a($squareResult, "Illuminate\Http\RedirectResponse")) {
            return $squareResult;
        } else {
            Log::error(get_class()." - Error Creating Square Checkout - $squareResult");

            return response(view('errors.generic',
                ['error_code' => 500,
                    'error_message' => 'Unable to process Square Checkout request.', ]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = Payment::find($id);
        if ($payment) {
            return response()->json(['status' => 'success', 'payment' => $payment]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => 'numeric',
            'method' => 'string',
            'recorded_by' => 'numeric|exists:users,id',
        ]);

        $payment = Payment::find($id);
        if ($payment) {
            $payment->update($request->all());
        } else {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }

        $payment = Payment::find($payment->id);
        if ($payment) {
            return response()->json(['status' => 'success', 'payment' => $payment]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);
        $deleted = $payment->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'Payment deleted.']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'Payment does not exist or was previously deleted.', ], 422);
        }
    }

    /**
     * Creates Square Checkout Payment Flow.
     *
     * @param $name string Name of line item
     * @param $amount integer Amount in *whole* dollars to be paid (excluding fees!)
     * @param $email string Email address for Square Receipt
     * @param $payment \App\Payment Payment Model
     * @param $addFee boolean Adds $3.00 transaction fee if true
     */
    public function createSquareCheckout($name, $amount, $email, $payment, $addFee)
    {
        $api = new CheckoutApi();
        $location = config('payment.square.location_id');
        $token = config('payment.square.token');

        $line_items = [
            [
                'name' => ($name) ?: 'Miscellaneous Payment',
                'quantity' => '1',
                'base_price_money' => [
                    'amount' => (int) $amount * 100,
                    'currency' => 'USD',
                ],
            ],
        ];

        if ($addFee) {
            $line_items[] =
            [
                'name' => 'Transaction Fee',
                'quantity' => '1',
                'base_price_money' => [
                    'amount' => 300,
                    'currency' => 'USD',
                ],
            ];
        }

        $order = new CreateOrderRequest([
            'reference_id' => "PMT$payment->id",
            'line_items' => $line_items,
        ]);

        $checkout_request = new CreateCheckoutRequest([
            'idempotency_key' => $payment->unique_id,
            'order' => $order,
            'merchant_support_email' => 'treasurer@robojackets.org',
            'pre_populate_buyer_email' => $email,
            'redirect_url' => route('payments.complete'),
        ]);

        try {
            Configuration::getDefaultConfiguration()->setAccessToken($token);
            $checkout = $api->createCheckout($location, $checkout_request);
        } catch (ApiException $e) {
            Bugsnag::notifyException($e);
            $message = $e->getResponseBody()->errors[0]->detail;

            return $message;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);

            return $e->getMessage();
        }

        if ($checkout) {
            $payment->checkout_id = $checkout['checkout']['id'];

            return redirect($checkout['checkout']['checkout_page_url']);
        }
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
            Log::warning(get_class().' - Missing parameter in Square response');

            return response(view('errors.generic',
                [
                    'error_code' => 400,
                    'error_message' => 'Missing parameter in Square response.',
                ]), 500);
        }

        $checkout_id = $request->input('checkoutId');
        $server_txn_id = $request->input('transactionId');
        $client_txn_id = $request->input('referenceId');

        //Check to make sure the reference ID is "PMTXXXX"
        $payment_id = substr($client_txn_id, 3);
        Log::debug(get_class()." - Stripping Reference ID '$client_txn_id' to '$payment_id'");
        if (! is_numeric($payment_id) || substr($client_txn_id, 0, 3) != 'PMT') {
            Log::error(get_class()." - Invalid Payment ID in Square response '$payment_id'");

            return response(view('errors.generic',
                [
                    'error_code' => 422,
                    'error_message' => 'Invalid Payment ID in Square response.',
                ]), 500);
        }

        //Find the payment
        $payment = Payment::find($payment_id);
        if (! $payment) {
            Log::warning(get_class()." - Error locating Payment '$payment_id'");

            return response(view('errors.generic',
                [
                    'error_code' => 404,
                    'error_message' => 'Unable to locate payment.',
                ]), 500);
        }
        Log::debug(get_class()." - Found Payment '$payment_id'");

        //Check if the payment has already been processed
        if ($payment->amount != 0 || $payment->checkout_id != null) {
            Log::warning(get_class()." - Payment Already Processed '$payment_id'");

            return response(view('errors.generic',
                [
                    'error_code' => 409,
                    'error_message' => 'Payment already processed.',
                ]), 500);
        }

        //Prepare Square API Call
        $txnClient = new TransactionsApi();
        $location = config('payment.square.location_id');
        $token = config('payment.square.token');
        Configuration::getDefaultConfiguration()->setAccessToken($token);

        //Query Square API to get authoritative data
        //See #284 for reasoning for loop
        $counter = 0;
        while ($counter < 4) {
            $square_txn = $this->getSquareTransaction($txnClient, $location, $server_txn_id);
            if (! $square_txn instanceof Exception) {
                break;
            }
            $counter++;
            sleep($counter * 0.1);
        }

        if ($square_txn instanceof Exception) {
            Bugsnag::notifyException($square_txn);

            return response(view('errors.generic',
                [
                    'error_code' => 500,
                    'error_message' => 'Error querying Square transaction',
                ]), 500);
        }

        $tenders = $square_txn->getTransaction()->getTenders();
        $amount = $tenders[0]->getAmountMoney()->getAmount() / 100;
        $proc_fee = $tenders[0]->getProcessingFeeMoney()->getAmount() / 100;
        $created_at = $square_txn->getTransaction()->getCreatedAt();
        Log::debug(get_class()." - Square Transaction Details for '$server_txn_id'",
            ['Amount' => $amount, 'Txn Date' => $created_at, 'Processing Fee' => $proc_fee]);

        //Compare received payment amount to expected payment amount
        $payable = $payment->payable;
        $expected_amount = $payable->getPayableAmount();
        $difference = $amount - $expected_amount;
        if ($difference != 3) {
            $message = "Payment Discrepancy Found for ID $payment->id";
            $data = ['Expected' => $expected_amount, 'Actual' => $amount, 'Server Txn ID' => $server_txn_id];
            Log::error(get_class().' - '.$message, $data);

            return response(view('errors.generic',
                [
                    'error_code' => 409,
                    'error_message' => 'Payment discrepancy found. Please contact the Treasurer for assistance.',
                ]), 500);
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

        Log::debug(get_class()."Payment $payment->id Updated Successfully");

        alert()->success("We've received your payment", 'Success!');

        return redirect('/');
    }

    /**
     * Queries Square Transaction API for transaction details.
     *
     * @param TransactionsApi $client
     * @param string $location
     * @param string $server_txn_id
     * @return \Exception|\SquareConnect\Model\RetrieveTransactionResponse
     */
    protected function getSquareTransaction(TransactionsApi $client, $location, $server_txn_id)
    {
        try {
            Log::debug(get_class()." - Querying Square for Transaction '$server_txn_id'");
            $square_txn = $client->retrieveTransaction($location, $server_txn_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $error = is_array($error) ? $error : [$error];
            Log::error(get_class().' - Error querying Square transaction', $error);

            return $e;
        }
        Log::debug(get_class()." - Retrieved Square Transaction '$server_txn_id'");

        return $square_txn;
    }
}
