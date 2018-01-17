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
        $needsTransaction = (DuesTransaction::current()->where('user_id', $user->id)->count() == 0);
        
        //User needs a payment if they don't have enough payments to cover their pending dues transaction
        //Don't change this to use ->count(). It won't work - trust me.
        $needsPayment = (count(DuesTransaction::pending()->where('user_id', $user->id)->get()) > 0);
        
        $data = ['needsTransaction' => $needsTransaction, 'needsPayment' => $needsPayment];
        return view('welcome', $data);
    }
}
