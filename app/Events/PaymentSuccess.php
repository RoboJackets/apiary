<?php declare(strict_types = 1);

namespace App\Events;

use App\Payment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PaymentSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The payment of interest
     *
     * @var \App\Payment
     */
    public $payment;

    /**
     * Create a new event instance.
     *
     * @param \App\Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
