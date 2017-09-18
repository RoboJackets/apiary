<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DuesTransaction;

class DuesTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transact = DuesTransaction::all();
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
            'received_polo' => 'boolean',
            'received_shirt' => 'boolean',
            'dues_package_id' => 'required|exists:dues_packages,id',
            'payment_id' => 'exists:payments,id',
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $transact = DuesTransaction::create($request->all());
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($transact->id)) {
            $dbTransact = DuesTransaction::findOrFail($transact->id);
            return response()->json(['status' => 'success', 'dues_transaction' => $dbTransact], 201);
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
        $transact = DuesTransaction::find($id);
        if ($transact) {
            return response()->json(['status' => 'success', 'dues_transaction' => $transact]);
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
            'received_polo' => 'boolean',
            'received_shirt' => 'boolean',
            'dues_package_id' => 'exists:dues_packages,id',
            'payment_id' => 'exists:payments,id',
            'user_id' => 'exists:users,id'
        ]);

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
                'message' => 'DuesTransaction does not exist or was previously deleted.'], 422);
        }
    }
}
