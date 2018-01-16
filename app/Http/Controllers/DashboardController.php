<?php

namespace App\Http\Controllers;

use App\DuesTransaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Returns view with data for the user dashboard
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        //User needs a transaction if they don't have one for an active dues package
        $user = $request->user();
        $transactionsQuery = DuesTransaction::where('user_id', $user->id)
            ->whereHas('package', function ($q) {
                $q->active();
            });
        $needsTransaction = (count($transactionsQuery->get()) == 0);
        
        /* User needs a payment if they either:
         * (1) Have a DuesTransaction for an active DuesPackage with payment less than payable amount, OR
         * (2) Have a DuesTransaction for an active DuesPackage without any payment attempts
         */
        //Get transactions with payments
        $txnWithPayment = $transactionsQuery->whereHas('payment')->get();
        if (count($txnWithPayment) > 0) {
            //Compare sum of payments for last transaction to package payable amount
            $paidSum = $txnWithPayment->last()->payment->sum('amount');
            $needsPayment = ($paidSum < $txnWithPayment->last()->getPayableAmount());
        } elseif ($needsTransaction == false && count($txnWithPayment) == 0) {
            //Transaction already exists, but no payment attempts have been made
            $needsPayment = true;
        } else {
            //Transaction already exists, full amount has been paid
            //I don't think we'll ever make it to this part of the conditional
            $needsPayment = false;
        }
        
        $data = ['needsTransaction' => $needsTransaction, 'needsPayment' => $needsPayment];
        return view('welcome', $data);
    }
}
