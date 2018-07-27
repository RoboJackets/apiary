<?php

namespace App\Listeners;

use Log;
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
                $user->removeRole('non-member');
                $user->assignRole('member');
            }
        }
    }
}
