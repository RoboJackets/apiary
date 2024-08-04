<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\MembershipAgreementTemplate;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Returns view with data for the user dashboard.
     */
    public function index(Request $request)
    {
        //User needs a transaction if they don't have one for an active dues package
        $user = $request->user();
        $preferredName = $user->preferred_first_name;
        $status = $user->is_active;

        $agreementExists = MembershipAgreementTemplate::exists();
        $signedLatestAgreement = $user->signed_latest_agreement;
        $signedAnyAgreement = $user->signatures()->where('complete', true)->exists();

        //User is "new" if they don't have any transactions, or they have never paid dues
        $paidTxnColl = DuesTransaction::paid()->where('user_id', $user->id)->get();

        $paidPackageIds = $paidTxnColl->map(
            static fn (DuesTransaction $transaction): int => $transaction->dues_package_id
        )->uniqueStrict()->toArray();

        $paidTxn = count($paidTxnColl);
        $isNew = ($user->dues->count() === 0 || ($user->dues->count() >= 1 && $paidTxn === 0));

        //User needs a transaction if they don't have one for an active dues package
        $needsTransaction = (DuesTransaction::current()
            ->where('user_id', $user->id)
            ->whereHas('package', static function (Builder $query) use ($user): void {
                $query->userCanPurchase($user);
            })
            ->count() === 0
        );
        $needsTransaction = $needsTransaction && (DuesPackage::userCanPurchase($user)->count() > 0) && ! $status;

        //User needs a payment if they don't have enough payments to cover their pending dues transaction
        //Don't change this to use ->count(). It won't work - trust me.
        $needsPayment = (count(
            DuesTransaction::pending()
                ->current()
                ->where('user_id', $user->id)
                ->whereHas('package', static function (Builder $query) use ($user): void {
                    $query->userCanPurchase($user);
                })
                ->whereNotIn('dues_package_id', $paidPackageIds)
                ->get()
        ) > 0);

        if (! $isNew) {
            $paidTransactions = DuesTransaction::select('dues_transactions.id', 'dues_transactions.dues_package_id')
                ->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('dues_transactions.id', '=', 'payable_id')
                        ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                        ->where('payments.amount', '>', 0);
                })
                ->where('user_id', $user->id)
                ->whereNotNull('payments.id')
                ->orderBy('payments.updated_at')
                ->with('package')
                ->with('payment')
                ->get();

            $firstPaidTransact = $paidTransactions->first();
            $lastPaidTransact = $paidTransactions->last();
            $packageEnd = date('F j, Y', strtotime($lastPaidTransact->package->effective_end->toDateTimeString()));
            $firstPayment = date(
                'F j, Y',
                strtotime($firstPaidTransact->payment->first()->created_at->toDateTimeString())
            );
        } else {
            $packageEnd = null;
            $firstPayment = null;
        }

        $hasOverride = $user->has_active_override;
        $hasExpiredOverride = ! $user->is_active && $user->access_override_until && $user->access_override_until < now()
            && $user->access_override_until > now()->startOfDay()->subDays(14);
        $overrideDate = $user->access_override_until ? $user->access_override_until->format('F j, Y') : 'n/a';

        $needsResume = $user->is_active &&
            (($user->resume_date && $user->resume_date < now()->startOfDay()->subDays(28)) || ! $user->resume_date)
            && config('features.resumes') === true
            && $user->is_student;

        $lastAttendance = $user->attendance()->where('attendable_type', Team::getMorphClassStatic())
            ->orderByDesc('created_at')->first();

        $sumsRequiresAgreement = config('sums.requires_agreement');

        $sumsAccessPending = $user->is_access_active
            && ! $user->exists_in_sums
            && $lastAttendance !== null
            // Not sure if this is an actual problem
            // @phan-suppress-next-line PhanTypeExpectedObjectPropAccessButGotNull
            && $lastAttendance->created_at > new Carbon(config('sums.attendance_timeout_limit'), 'America/New_York')
            && ($signedLatestAgreement || $sumsRequiresAgreement !== true);

        $travelAssignment = $user->current_travel_assignment;

        return view(
            'welcome',
            [
                'needsTransaction' => $needsTransaction,
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
                'signedLatestAgreement' => $signedLatestAgreement,
                'signedAnyAgreement' => $signedAnyAgreement,
                'agreementExists' => $agreementExists,
                'travelAssignment' => $travelAssignment,
                'hasEmergencyContactInformation' => $user->has_emergency_contact_information,
            ]
        );
    }
}
