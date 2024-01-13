<?php

declare(strict_types=1);

namespace App\Jobs;

use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessPostmarkInboundWebhook extends ProcessWebhookJob
{
    /**
     * The queue this job will run on. This is fairly arbitrary since it only touches the local DB.
     *
     * @var string
     */
    public $queue = 'email';

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
        $payload = $this->webhookCall->payload;
        $subject = $payload['Subject'];

        if ($subject !== 'Test subject') {
            $this->fail('Inbound email is not currently supported');
        }
    }
}
