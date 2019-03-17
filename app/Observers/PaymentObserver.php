<?php

namespace App\Observers;

use App\Payment;
use App\Jobs\PushToJedi;

class PaymentObserver
{
    public function saved(Payment $payment) {
        PushToJedi::dispatch($payment->payable->user)->onQueue('jedi');
    }
}
