<?php

declare(strict_types=1);

// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.NoSpaceAfter
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.NoSpaceBefore

namespace App\Jobs;

use App\Models\DocuSignEnvelope;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Facades\Storage;
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
     * @phan-suppress PhanPossiblyFalseTypeArgument
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

        $data = $this->webhookCall->payload['data'];

        if (array_key_exists('envelopeSummary', $data)) {
            if (array_key_exists('completedDateTime', $data['envelopeSummary'])) {
                $envelope->completed_at = Carbon::parse($data['envelopeSummary']['completedDateTime'])
                    ->setTimezone(config('app.timezone'));
            }

            if (
                array_key_exists('sender', $data['envelopeSummary']) &&
                array_key_exists('email', $data['envelopeSummary']['sender'])
            ) {
                $sender_email = $data['envelopeSummary']['sender']['email'];

                $sender_email_parts = explode('@', @$sender_email);

                try {
                    $user = User::where('uid', '=', $sender_email_parts[0])->sole();

                    $envelope->sent_by = $user->id;
                } catch (ModelNotFoundException|MultipleRecordsFoundException) {
                    // do nothing
                }
            }

            if (
                array_key_exists('recipients', $data['envelopeSummary']) &&
                array_key_exists('signers', $data['envelopeSummary']['recipients']) &&
                count($data['envelopeSummary']['recipients']['signers']) === 1
            ) {
                $recipient = $data['envelopeSummary']['recipients']['signers'][0];

                if (array_key_exists('sentDateTime', $recipient)) {
                    $envelope->sent_at = Carbon::parse($recipient['sentDateTime'])
                        ->setTimezone(config('app.timezone'));
                }

                if (array_key_exists('deliveredDateTime', $recipient)) {
                    $envelope->viewed_at = Carbon::parse($recipient['deliveredDateTime'])
                        ->setTimezone(config('app.timezone'));
                }

                if (array_key_exists('signedDateTime', $recipient)) {
                    $envelope->signed_at = Carbon::parse($recipient['signedDateTime'])
                        ->setTimezone(config('app.timezone'));
                }
            }

            if (array_key_exists('envelopeDocuments', $data['envelopeSummary'])) {
                foreach ($data['envelopeSummary']['envelopeDocuments'] as $document) {
                    $disk_path = 'docusign/'.$docusignEnvelopeId.'/'.$document['name'].'.pdf';

                    Storage::disk('local')->put($disk_path, base64_decode($document['PDFBytes'], true));

                    if ($document['type'] === 'summary') {
                        $envelope->summary_filename = $disk_path;
                    } elseif ($document['name'] === 'Travel Information Form') {
                        $envelope->travel_authority_filename = $disk_path;
                    } elseif (str_contains($document['name'], 'Airfare')) {
                        $envelope->direct_bill_airfare_filename = $disk_path;
                    }
                }
            }
        }

        if (array_key_exists('status', $data['envelopeSummary'])) {
            switch ($data['envelopeSummary']['status']) {
                case 'signed':
                case 'completed':
                    $envelope->complete = true;
                    $envelope->save();

                    break;
                case 'declined':
                case 'voided':
                    $envelope->save();

                    if ($envelope->deleted_at === null) {
                        $envelope->delete();
                    }

                    break;
                case 'created':
                case 'delivered':
                case 'sent':
                    $envelope->save();

                    break;
                default:
                    throw new Exception('Unrecognized event status '.$data['envelopeSummary']['status']);
            }
        }
    }
}
