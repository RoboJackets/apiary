<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DocuSignEnvelope;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessDocuSignWebhook extends ProcessWebhookJob
{
    /**
     * The queue this job will run on. This is fairly arbitrary since it only touches the local DB.
     *
     * @var string
     */
    public $queue = 'docusign';

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
        $internalEnvelopeId = $this->webhookCall->payload['internalEnvelopeId'];
        $docusignEnvelopeId = $this->webhookCall->payload['data']['envelopeId'];

        $envelope = DocuSignEnvelope::where('envelope_id', '=', $docusignEnvelopeId)
            ->withTrashed()
            ->first();

        if ($envelope === null) {
            $envelope = DocuSignEnvelope::where('id', '=', $internalEnvelopeId)
                ->whereNull('envelope_id')
                ->withTrashed()
                ->sole();
            $envelope->envelope_id = $docusignEnvelopeId;
            $envelope->save();
        }

        $event = $this->webhookCall->payload['event'];

        switch ($event) {
            case 'recipient-completed':
            case 'envelope-completed':
                if (! $envelope->complete) {
                    $envelope->complete = true;
                    $envelope->save();
                }

                break;
            case 'recipient-declined':
            case 'envelope-declined':
            case 'envelope-voided':
            case 'envelope-deleted':
                if ($envelope->deleted_at !== null) {
                    $envelope->delete();
                }

                break;
        }
    }
}
