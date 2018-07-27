<?php

namespace App\Listeners;

use Log;
use Spatie\Permission\Models\Role;
use App\DuesTransaction;
use App\Events\PaymentSuccess;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentSuccessListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PaymentSuccess  $event
     * @return void
     */
    public function handle(PaymentSuccess $event)
    {
        $payment = $event->payment;
        $payable = $payment->payable;
        Log::info(get_class() . ": Handling successful payment ID " . $payment->id);
        // If this is a Dues Transaction payment, update user roles
        if ($payable instanceof DuesTransaction) {
            if ($payable->status == "paid") {
                $user = $payable->user;
                Log::info(get_class() . ": Updating role membership for " . $user->uid);
                if ($user->hasRole('non-member')) {
                    $user->removeRole('non-member');
                }
                $role_member = Role::where('name', 'member')->first();
                if ($role_member) {
                    $user->assignRole($role_member);
                } else {
                    Log::error(get_class()."Role 'member' not found for assignment to $user->uid.");
                }
            }
        }
    }
}
