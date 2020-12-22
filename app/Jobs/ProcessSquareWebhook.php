<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\PaymentSuccess;
use App\Models\Payment;
use App\Notifications\Payment\ConfirmationNotification;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessSquareWebhook extends ProcessWebhookJob
{

    /**
     * The queue this job will run on. This is fairly arbitrary since it only touches the local DB.
     *
     * @var string
     */
    public $queue = 'square';

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ('payment.created' !== $this->webhookCall->payload['type']) {
            return;
        }

        $details = $this->webhookCall->payload['data']['object']['payment'];

        $payment = Payment::firstOrFail($details['reference_id']);
        $payment->processing_fee = $details['processing_fee'];
        $payment->amount = $details['amount_money']['amount'] / 100;
        $payment->order_id = $details['order_id'];
        $payment->save();

        $payment->payable->user->notify(new ConfirmationNotification($payment));
        event(new PaymentSuccess($payment));
    }
}
