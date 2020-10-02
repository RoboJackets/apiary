<?php

declare(strict_types=1);

namespace App\Events;

use App\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccess
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The payment of interest.
     *
     * @var \App\Payment
     */
    public $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
