<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDuesTransactionRequest;
use App\Http\Requests\UpdateDuesTransactionRequest;
use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Merchandise;
use App\Models\User;
use App\Util\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DuesTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            'permission:read-dues-transactions',
            ['only' => ['index', 'indexPaid', 'indexPending']]
        );
        $this->middleware('permission:create-dues-transactions-own|create-dues-transactions', ['only' => ['store']]);
        $this->middleware(
            'permission:read-dues-transactions|read-dues-transactions-own',
            ['only' => ['show']]
        );
        $this->middleware('permission:update-dues-transactions', ['only' => ['update']]);
        $this->middleware('permission:delete-dues-transactions', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $transact = DuesTransaction::with(AuthorizeInclude::authorize(DuesTransaction::class, $include))->get();
        $transact = DuesTransactionResource::collection($transact);

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Display a listing of paid resources.
     */
    public function indexPaid(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $transact = DuesTransaction::paid()->with(AuthorizeInclude::authorize(DuesTransaction::class, $include))->get();
        $transact = DuesTransactionResource::collection($transact);

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Display a listing of pending resources.
     */
    public function indexPending(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $transact = DuesTransaction::pending()->with(
            AuthorizeInclude::authorize(DuesTransaction::class, $include)
        )->get();
        $transact = DuesTransactionResource::collection($transact);

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDuesTransactionRequest $request): JsonResponse
    {
        $user = $request->user();
        $user_id = $request->input('user_id');

        // Make sure that the user is actually allowed to create this transaction
        if ($request->filled('user_id') && $user_id !== $user->id && (! $user->can('create-dues-transactions'))) {
            return response()->json(['status' => 'error',
                'message' => 'You may not create a DuesTransaction for another user.',
            ], 403);
        }

        if (! $request->filled('user_id')) {
            $request->merge(['user_id' => $user->id]);
        }

        if (! User::where('id', $request->input('user_id'))->sole()->signed_latest_agreement) {
            return response()->json(['status' => 'error',
                'message' => 'User has not signed latest agreement',
            ], 422);
        }

        if ($request->filled('merchandise')) {
            $selectedMerch = collect($request->input('merchandise'));
            $package = DuesPackage::where('id', $request->input('dues_package_id'))->sole();
            $groups = $package->merchandise->groupBy(static fn (Merchandise $merch): string => $merch->pivot->group);

            if (count($selectedMerch) !== $groups->count()) {
                return response()->json(['status' => 'error',
                    'message' => 'You must select one item of merchandise from every group.',
                ], 422);
            }

            if (count($selectedMerch) !== $selectedMerch->unique()->count()) {
                return response()->json(['status' => 'error',
                    'message' => 'You cannot select duplicate merchandise.',
                ], 422);
            }

            // For every merch group, ensure that one of the selected merch is in it. This assumes that merch is not
            // in multiple groups.
            $valid = $groups->every(
                static fn (Collection $collection): bool => $selectedMerch->contains(
                    static fn (int $selectedID): bool => $collection->contains('id', $selectedID)
                )
            );

            if (! $valid) {
                return response()->json(['status' => 'error',
                    'message' => 'You must select one item of merchandise from every group.',
                ], 422);
            }

            if (! $user->has_ordered_polo) {
                // For each group, ensure that if there is a polo, the user selected it.
                // This assumes that there are not multiple Merchandise in a group that start with Polo.
                $selectedPolo = $groups->every(static function (Collection $collection) use ($selectedMerch): bool {
                    $polo = $collection->first(
                        static fn (Merchandise $merch): bool => Str::startsWith($merch->name, 'Polo ')
                    );

                    if ($polo !== null) {
                        return $selectedMerch->contains($polo->id);
                    }

                    return true;
                });

                if (! $selectedPolo) {
                    return response()->json(['status' => 'error',
                        'message' => 'You have not ordered a polo before, so you must order a polo.',
                    ]);
                }
            }
        }

        // If there's an existing active transaction that hasn't been paid, delete it
        // and replace it with the one currently being requested
        if ($user->dues->count() > 0) {
            $existingTransaction = $user->dues()->latest()->first();
            $pkgIsActive = $existingTransaction->package->is_active;
            if ($pkgIsActive) {
                $hasPayment = $existingTransaction->payment()->exists();
                if ($hasPayment) {
                    $paidAny = ($existingTransaction->payment->sum('amount') > 0);
                    if (! $paidAny) {
                        $existingTransaction->delete();
                    }
                } else {
                    $existingTransaction->delete();
                }
            }
        }

        $transact = DuesTransaction::create([
            'user_id' => $request->user()->id,
            'dues_package_id' => $request->validated('dues_package_id'),
        ]);

        $dbTransact = DuesTransaction::findOrFail($transact->id);

        if ($request->filled('merchandise')) {
            $selectedMerch = collect($request->input('merchandise'));
            $selectedMerch->each(static function (int $merch) use ($dbTransact): void {
                $dbTransact->merchandise()->attach(Merchandise::find($merch));
            });
        }

        $dbTransact = new DuesTransactionResource($dbTransact);

        return response()->json(['status' => 'success', 'dues_transaction' => $dbTransact], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, DuesTransaction $transaction): JsonResponse
    {
        $requestingUser = $request->user();
        $include = $request->input('include');
        $transact = DuesTransaction::with(
            AuthorizeInclude::authorize(DuesTransaction::class, $include)
        )->find($transaction->id);
        if ($transact === null) {
            return response()->json(['status' => 'error', 'message' => 'DuesTransaction not found.'], 404);
        }

        $requestedUser = $transact->user;
        // Enforce users only viewing their own DuesTransactions (read-dues-transactions-own)
        if ($requestingUser->cant('read-dues-transactions') && $requestingUser->id !== $requestedUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden - You do not have permission to view this DuesTransaction.',
            ], 403);
        }

        $transact = new DuesTransactionResource($transact);

        return response()->json(['status' => 'success', 'dues_transaction' => $transact]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDuesTransactionRequest $request, DuesTransaction $transaction): JsonResponse
    {
        $transaction->update($request->validated());

        return response()->json(
            [
                'status' => 'success',
                'dues_transaction' => new DuesTransactionResource(DuesTransaction::findOrFail($transaction->id)),
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DuesTransaction $transaction): JsonResponse
    {
        if ($transaction->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'DuesTransaction deleted.']);
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'DuesTransaction does not exist or was previously deleted.',
            ],
            422
        );
    }

    public function showDuesFlow(Request $request)
    {
        if ($request->user()->is_active) {
            return view('dues.alreadypaid');
        }

        if (! $request->user()->signed_latest_agreement) {
            return view('dues.agreementrequired');
        }

        return view('dues.flow');
    }
}
