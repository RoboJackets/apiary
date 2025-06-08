<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\PaymentReceived;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\Payment as PaymentResource;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PaymentController implements HasMiddleware
{
    #[\Override]
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read-payments', only: ['index']),
            new Middleware('permission:create-payments|create-payments-own', only: ['store']),
            new Middleware('permission:read-payments|read-payments-own', only: ['show', 'indexForUser']),
            new Middleware('permission:update-payments', only: ['update']),
            new Middleware('permission:delete-payments', only: ['destroy']),
        ];
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
     * Display a list of payments for a given user.
     */
    public function indexForUser(Request $request, User $user): JsonResponse
    {
        $requestingUser = $request->user();

        $userId = $user->id;

        if ($userId !== $requestingUser->id && $requestingUser->cannot('read-payments')) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Forbidden - you do not have permission to view payments for other users',
                ],
                403
            );
        }

        $duesTransactions = Payment::wherePayableType(DuesTransaction::getMorphClassStatic())
            ->with('duesTransaction', 'duesTransaction.package', 'recordedBy')
            ->whereHas('duesTransaction.user', static function (Builder $q) use ($userId) {
                $q->whereId($userId);
            })
            ->where(static function (Builder $q) {
                $q->where('amount', '>', 0)
                    ->orWhereNotNull('card_brand');
            })
            ->orderBy('updated_at')
            ->get();

        $travelAssignments = Payment::wherePayableType(TravelAssignment::getMorphClassStatic())
            ->with('travelAssignment', 'travelAssignment.travel', 'recordedBy')
            ->whereHas('travelAssignment.user', static function (Builder $q) use ($userId) {
                $q->whereId($userId);
            })
            ->where(static function (Builder $q) {
                $q->where('amount', '>', 0)
                    ->orWhereNotNull('card_brand');
            })
            ->orderBy('updated_at')
            ->get();

        $combined = $duesTransactions->concat($travelAssignments)->sortBy('updated_at', descending: true);

        return response()->json([
            'status' => 'success',
            'payments' => PaymentResource::collection($combined),
        ]);
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

        event(new PaymentReceived($dbPayment));

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
        if ($payment !== null) {
            return response()->json(['status' => 'success', 'payment' => $payment]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        if ($payment->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'Payment deleted.']);
        }

        return response()->json(['status' => 'error',
            'message' => 'Payment does not exist or was previously deleted.',
        ], 422);
    }
}
