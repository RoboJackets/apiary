<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Payment;
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

        if (!$request->has('recorded_by') ||
            $currentUser->cant('update-payments')) {
            $request['recorded_by'] = $currentUser->id;
        }

        $this->validate($request, [
            'amount' => 'required|numeric',
            'method' => 'required|string',
            'recorded_by' => 'numeric|exists:users,id',
            'payable_type' => 'required|string',
            'payable_id' => 'required|numeric'
        ]);

        try {
            $payment = Payment::create($request->all());
        } catch (QueryException $e) {
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
            'recorded_by' => 'numeric|exists:users,id'
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
                'message' => 'Payment does not exist or was previously deleted.'], 422);
        }
    }
}
