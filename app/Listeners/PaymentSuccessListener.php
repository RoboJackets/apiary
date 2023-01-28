<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentSuccess;
use App\Models\DuesTransaction;
use App\Models\TravelAssignment;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class PaymentSuccessListener
{
    /**
     * Handle the event.
     */
    public function handle(PaymentSuccess $event): void
    {
        $payment = $event->payment;
        $payable = $payment->payable;
        Log::info(self::class.': Handling successful payment ID '.$payment->id);

        if (
            ($payment->payable_type === DuesTransaction::getMorphClassStatic() && $payable->status !== 'paid') ||
            ($payment->payable_type === TravelAssignment::getMorphClassStatic() && ! $payable->is_paid)
        ) {
            return;
        }

        $user = $payable->user;
        Log::info(self::class.': Updating role membership for '.$user->uid);
        if ($user->hasRole('non-member')) {
            $user->removeRole('non-member');
        }
        $role_member = Role::where('name', 'member')->first();
        if ($role_member !== null && ! $user->hasRole('member')) {
            $user->assignRole($role_member);
        } elseif ($user->hasRole('member')) {
            Log::notice(self::class.": Role 'member' already assigned to ".$user->uid);
        } else {
            Log::error(self::class.": Role 'member' not found for assignment to ".$user->uid);
        }
    }
}
