<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\PushToJedi;
use App\Models\Payment;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        PushToJedi::dispatch($payment->payable->user, Payment::class, $payment->id, 'saved');
    }
}
