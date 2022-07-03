<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\PaymentSuccess;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

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

        $payment = Payment::create($request->validated());

        $dbPayment = Payment::findOrFail($payment->id);

        event(new PaymentSuccess($dbPayment));

        return response()->json(['status' => 'success', 'payment' => $dbPayment], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json(['status' => 'success', 'payment' => $payment]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());

        $payment = Payment::find($payment->id);
        if (null !== $payment) {
            return response()->json(['status' => 'success', 'payment' => $payment]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        if (true === $payment->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Payment deleted.']);
        }

        return response()->json(['status' => 'error',
            'message' => 'Payment does not exist or was previously deleted.',
        ], 422);
    }
}
