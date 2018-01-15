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
        $user = $request->user();
        $needsPayment = $user->needsPayment;
        $transactions = DuesTransaction::where('user_id', $user->id)
            ->whereHas('package', function ($q) {
                $q->whereDate('effective_start', '<=', date('Y-m-d'))
                    ->whereDate('effective_end', '>=', date('Y-m-d'));
            })->get();
        $needsTransaction = (count($transactions) == 0);
        $data = ['needsTransaction' => $needsTransaction, 'needsPayment' => $needsPayment];
        return view('welcome', $data);
    }
}
