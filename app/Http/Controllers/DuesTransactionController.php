<?php

namespace App\Http\Controllers;

use App\User;
use App\DuesTransaction;
use Illuminate\Http\Request;
use App\Notifications\Dues\RequestCompleteNotification as Confirm;

class DuesTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-dues-transactions',
            ['only' => ['index', 'indexPaid', 'indexPending', 'indexPendingSwag']]);
        $this->middleware('permission:create-dues-transactions', ['only' => ['store']]);
        $this->middleware('permission:read-dues-transactions|read-dues-transactions-own',
            ['only' => ['show']]);
        $this->middleware('permission:update-dues-transactions', ['only' => ['update']]);
        $this->middleware('permission:delete-dues-transactions', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transact = DuesTransaction::all();
        $transact->load(['user', 'package', 'payment']);

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Display a listing of paid resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexPaid()
    {
        $transact = DuesTransaction::paid()->with(['user', 'package'])->get();

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Display a listing of pending resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexPending()
    {
        $transact = DuesTransaction::pending()->with(['user', 'package'])->get();

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Display a listing of swag pending resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexPendingSwag()
    {
        $transact = DuesTransaction::pendingSwag()->with(['user', 'package'])->get();

        return response()->json(['status' => 'success', 'dues_transactions' => $transact]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'swag_shirt_provided' => 'boolean|nullable',
            'swag_polo_provided' => 'boolean|nullable',
            'dues_package_id' => 'required|exists:dues_packages,id',
            'payment_id' => 'exists:payments,id',
            'user_id' => 'exists:users,id',
        ]);

        $user = $request->user();
        $user_id = $request->input('user_id');

        //Make sure that the user is actually allowed to create this transaction
        if ($request->has('user_id') && $user_id != $user->id && (! $user->is_admin)) {
            return response()->json(['status' => 'error',
                'message' => 'You may not create a DuesTransaction for another user.', ], 403);
        } elseif (! $request->has('user_id')) {
            $request->merge(['user_id' => $user->id]);
        }

        //Translate boolean from client to time/date stamp for DB
        //Also set "providedBy" for each swag item to the submitting user
        $swagItems = ['swag_shirt_provided', 'swag_polo_provided'];
        foreach ($swagItems as $item) {
            if ($request->exists($item)) {
                $provided = $request->input($item);
                if ($provided !== null && $provided == true) {
                    $now = date('Y-m-d H:i:s');
                    $request->merge([$item => $now, $item.'By' => $request->user()->id]);
                } else {
                    //Remove the parameter from the request to avoid overwriting existing data
                    unset($request[$item]);
                }
            }
        }

        //Check to make sure there isn't already an existing package for the target user
        $existingTransaction = DuesTransaction::where('dues_package_id', $request->input('dues_package_id'))
            ->where('user_id', $request->input('user_id'))->first();
        if ($existingTransaction) {
            return response()->json(['status' => 'error',
                'message' => 'There is already a pending Dues Transaction for this user', ], 400);
        }

        try {
            $transact = DuesTransaction::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($transact->id)) {
            $dbTransact = DuesTransaction::findOrFail($transact->id);

            $user->notify(new Confirm($dbTransact->package));

            return response()->json(['status' => 'success', 'dues_transaction' => $dbTransact], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $requestingUser = $request->user();
        $transact = DuesTransaction::find($id);
        if (! $transact) {
            return response()->json(['status' => 'error', 'message' => 'DuesTransaction not found.'], 404);
        }

        $transact->load('user', 'package', 'payment');

        $requestedUser = $transact->user;
        //Enforce users only viewing their own DuesTransactions (read-dues-transactions-own)
        if ($requestingUser->cant('read-dues-transactions') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to view this DuesTransaction.', ], 403);
        }

        return response()->json(['status' => 'success', 'dues_transaction' => $transact]);
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
            'swag_shirt_provided' => 'boolean|nullable',
            'swag_polo_provided' => 'boolean|nullable',
            'dues_package_id' => 'exists:dues_packages,id',
            'payment_id' => 'exists:payments,id',
            'user_id' => 'exists:users,id',
        ]);

        //Translate boolean from client to time/date stamp for DB
        //Also set "providedBy" for each swag item to the submitting user
        $swagItems = ['swag_shirt_provided', 'swag_polo_provided'];
        foreach ($swagItems as $item) {
            if ($request->exists($item)) {
                $provided = $request->input($item);
                if ($provided !== null && $provided == true) {
                    $now = date('Y-m-d H:i:s');
                    $request->merge([$item => $now, $item.'By' => $request->user()->id]);
                } else {
                    //Remove the parameter from the request to avoid overwriting existing data
                    unset($request[$item]);
                }
            }
        }

        $transact = DuesTransaction::find($id);
        if ($transact) {
            $transact->update($request->all());
        } else {
            return response()->json(['status' => 'error', 'message' => 'DuesTransaction not found.'], 404);
        }

        $transact = DuesTransaction::find($transact->id);
        if ($transact) {
            return response()->json(['status' => 'success', 'dues_transaction' => $transact]);
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
        $transact = DuesTransaction::find($id);
        $deleted = $transact->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'DuesTransaction deleted.']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'DuesTransaction does not exist or was previously deleted.', ], 422);
        }
    }
}
