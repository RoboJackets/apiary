<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DuesPackage;
use App\DuesTransaction;
use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Returns view with data for the user dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        //User needs a transaction if they don't have one for an active dues package
        $user = $request->user();
        $preferredName = $user->preferred_first_name;
        $status = $user->is_active;

        //User is "new" if they don't have any transactions, or they have never paid dues
        $paidTxn = count(DuesTransaction::paid()->where('user_id', $user->id)->get());
        $isNew = (0 === $user->dues->count() || ($user->dues->count() >= 1 && 0 === $paidTxn));

        //User needs a transaction if they don't have one for an active dues package
        $needsTransaction = (0 === DuesTransaction::current()->where('user_id', $user->id)->count());
        $needsTransaction = $needsTransaction && (DuesPackage::availableForPurchase()->count() > 0);

        //User needs a payment if they don't have enough payments to cover their pending dues transaction
        //Don't change this to use ->count(). It won't work - trust me.
        $needsPayment = (count(DuesTransaction::pending()->where('user_id', $user->id)->get()) > 0);

        if (! $isNew) {
            $firstPaidTransact = DuesTransaction::paid()->where('user_id', $user->id)->with('package')->first();
            $lastPaidTransact = DuesTransaction::paid()->where('user_id', $user->id)->with('package')->get()->last();
            $packageEnd = date('F j, Y', strtotime($lastPaidTransact->package->effective_end->toDateTimeString()));
            $firstPayment = date(
                'F j, Y',
                strtotime($firstPaidTransact->payment->first()->created_at->toDateTimeString())
            );
        } else {
            $packageEnd = null;
            $firstPayment = null;
        }

        $hasOverride = ! $user->is_active && $user->access_override_until && $user->access_override_until > now();
        $hasExpiredOverride = ! $user->is_active && $user->access_override_until && $user->access_override_until < now()
            && $user->access_override_until > now()->startOfDay()->subDays(14);
        $overrideDate = $user->access_override_until ? $user->access_override_until->format('F j, Y') : 'n/a';

        $needsResume = $user->is_active &&
            (($user->resume_date && $user->resume_date < now()->startOfDay()->subDays(28)) || ! $user->resume_date);

        $sumsAccessPending = $user->is_access_active && ! $user->exists_in_sums;

        $data = ['needsTransaction' => $needsTransaction,
            'needsPayment' => $needsPayment,
            'status' => $status,
            'packageEnd' => $packageEnd,
            'firstPayment' => $firstPayment,
            'preferredName' => $preferredName,
            'isNew' => $isNew,
            'hasOverride' => $hasOverride,
            'hasExpiredOverride' => $hasExpiredOverride,
            'overrideDate' => $overrideDate,
            'needsResume' => $needsResume,
            'githubInvitePending' => $user->github_invite_pending,
            'sumsAccessPending' => $sumsAccessPending,
        ];

        return view('welcome', $data);
    }
}
