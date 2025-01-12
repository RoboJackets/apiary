<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentDeleted {
    public function __construct(public readonly Payment $payment)
    {
    }
}