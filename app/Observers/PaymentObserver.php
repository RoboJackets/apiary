<?php declare(strict_types = 1);

namespace App\Observers;

use App\Payment;
use App\Jobs\PushToJedi;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        PushToJedi::dispatch($payment->payable->user)->onQueue('jedi');
    }
}
