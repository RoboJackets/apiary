<?php

namespace App\Http\Controllers;

use App\DuesTransaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Returns view with data for the user dashboard.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        //User needs a transaction if they don't have one for an active dues package
        $user = $request->user();
        $preferredName = $user->preferred_first_name;
        $status = $user->is_active;

        //User is "new" if they don't have any transactions, or they have only one and it's unpaid
        $currentUnpaidTxn = count(DuesTransaction::current()->unpaid()->where('user_id', $user->id)->get());
        $isNew = ($user->dues->count() == 0 || ($user->dues->count() == 1 && $currentUnpaidTxn == 1));

        //User needs a transaction if they don't have one for an active dues package
        $needsTransaction = (DuesTransaction::current()->where('user_id', $user->id)->count() == 0);

        //User needs a payment if they don't have enough payments to cover their pending dues transaction
        //Don't change this to use ->count(). It won't work - trust me.
        $needsPayment = (count(DuesTransaction::pending()->where('user_id', $user->id)->get()) > 0);

        if (! $isNew) {
            $firstPaidTransact = DuesTransaction::paid()->where('user_id', $user->id)->with('package')->first();
            $lastPaidTransact = DuesTransaction::paid()->where('user_id', $user->id)->with('package')->get()->last();
            $packageEnd = date('F j, Y', strtotime($lastPaidTransact->package->effective_end));
            $firstPayment = date('F j, Y', strtotime($firstPaidTransact->payment->first()->created_at));
        } else {
            $firstPaidTransact = null;
            $lastPaidTransact = null;
            $packageEnd = null;
            $firstPayment = null;
        }

        $data = ['needsTransaction' => $needsTransaction,
            'needsPayment' => $needsPayment,
            'status' => $status,
            'packageEnd' => $packageEnd,
            'firstPayment' => $firstPayment,
            'preferredName' => $preferredName,
            'isNew' => $isNew, ];

        return view('welcome', $data);
    }
}
