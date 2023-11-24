<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\PaymentReceived;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

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
     *
     * @phan-suppress PhanTypeArraySuspiciousNullable
     */
    public function handle(): void
    {
        $type = $this->webhookCall->payload['type'];
        $details = $this->webhookCall->payload['data']['object']['payment'];
        $refunded = array_key_exists('refunded_money', $details);

        if (! array_key_exists('status', $details)) {
            throw new Exception('data.object.payment.status field not present');
        }

        if ($details['status'] !== 'COMPLETED' && $details['status'] !== 'APPROVED') {
            Log::warning('Payment for Order ID '.$details['order_id'].' was pushed as '.$details['status']);

            return;
        }

        $payment = Payment::where('order_id', $details['order_id'])->first();

        if ($payment === null) {
            Log::warning('Payment object with Order ID '.$details['order_id'].' not found, ignoring');

            return;
        }

        if ($refunded) {
            if ($details['amount_money']['amount'] < $details['refunded_money']['amount']) {
                Log::warning('Payment for Order ID '.$details['order_id'].' was partially refunded');
            }

            $payment->amount = ($details['amount_money']['amount'] - $details['refunded_money']['amount']) / 100;
        } else {
            $payment->amount = $details['amount_money']['amount'] / 100;

            if (array_key_exists('processing_fee', $details)) {
                $payment->processing_fee = $details['processing_fee'][0]['amount_money']['amount'] / 100;
            }
        }

        if (array_key_exists('receipt_number', $details)) {
            $payment->receipt_number = $details['receipt_number'];
        }

        if (array_key_exists('receipt_url', $details)) {
            $payment->receipt_url = $details['receipt_url'];
        }

        if (array_key_exists('card_details', $details)) {
            if (array_key_exists('entry_method', $details['card_details'])) {
                $payment->entry_method = $details['card_details']['entry_method'];
            }

            if (array_key_exists('statement_description', $details['card_details'])) {
                $payment->statement_description = $details['card_details']['statement_description'];
            }

            if (array_key_exists('card', $details['card_details'])) {
                $payment->card_brand = $details['card_details']['card']['card_brand'];
                $payment->card_type = $details['card_details']['card']['card_type'];
                $payment->last_4 = $details['card_details']['card']['last_4'];

                if (array_key_exists('prepaid_type', $details['card_details']['card'])) {
                    $payment->prepaid_type = $details['card_details']['card']['prepaid_type'];
                }
            }

            if (array_key_exists('entry_method', $details['card_details'])) {
                $payment->entry_method = $details['card_details']['entry_method'];
            }
        }

        if (! $refunded) {
            $payment->notes = 'Checkout flow completed';
        }

        $payment->save();

        if ($type !== 'payment.created' || $refunded) {
            return;
        }

        event(new PaymentReceived($payment));

        FulfillSquareOrder::dispatch($details['order_id']);
    }
}
