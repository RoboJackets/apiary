<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Observers;

use App\Jobs\PruneDuesNotificationsInNova;
use App\Jobs\PushToJedi;
use App\Jobs\SendDuesPaymentReminder;
use App\Models\DuesTransaction;
use App\Models\Payment;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        PushToJedi::dispatch($payment->payable->user, Payment::class, $payment->id, 'saved');

        $payment->payable->user->searchable();

        PruneDuesNotificationsInNova::dispatch($payment->payable->user);

        if ($payment->payable_type === DuesTransaction::getMorphClassStatic() &&
            0 === intval($payment->amount)
        ) {
            SendDuesPaymentReminder::dispatch($payment->payable->user);
        }
    }
}
