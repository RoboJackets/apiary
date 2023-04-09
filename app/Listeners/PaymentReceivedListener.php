<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Models\DuesTransaction;
use App\Models\TravelAssignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

/**
 * Handles role assignments once payment is received.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class PaymentReceivedListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
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
