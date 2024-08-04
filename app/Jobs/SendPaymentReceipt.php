<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Payment;
use App\Notifications\PaymentReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentReceipt implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Payment $payment)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->payment->payable->user->notify(new PaymentReceipt($this->payment));
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->payment->id);
    }
}
