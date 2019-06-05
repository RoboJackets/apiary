<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\DuesTransaction;
use App\Events\PaymentSuccess;
use Spatie\Permission\Models\Role;

class PaymentSuccessListener
{
    /**
     * Handle the event.
     *
     * @param \App\Events\PaymentSuccess  $event
     *
     * @return void
     */
    public function handle(PaymentSuccess $event): void
    {
        $payment = $event->payment;
        $payable = $payment->payable;
        Log::info(self::class.': Handling successful payment ID '.$payment->id);
        // If this is a Dues Transaction payment, update user roles
        if (! ($payable instanceof DuesTransaction)) {
            return;
        }

        if ('paid' !== $payable->status) {
            return;
        }

        $user = $payable->user;
        Log::info(self::class.': Updating role membership for '.$user->uid);
        if ($user->hasRole('non-member')) {
            $user->removeRole('non-member');
        }
        $role_member = Role::where('name', 'member')->first();
        if ($role_member && ! $user->hasRole('member')) {
            $user->assignRole($role_member);
        } elseif ($user->hasRole('member')) {
            Log::notice(self::class.": Role 'member' already assigned to ".$user->uid);
        } else {
            Log::error(self::class.": Role 'member' not found for assignment to ".$user->uid);
        }
    }
}
