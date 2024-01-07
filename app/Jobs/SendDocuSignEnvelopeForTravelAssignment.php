<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DocuSignEnvelope;
use App\Models\TravelAssignment;
use App\Util\DocuSign;
use DocuSign\eSign\Api\EnvelopesApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SendDocuSignEnvelopeForTravelAssignment implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly TravelAssignment $assignment)
    {
        $this->queue = 'docusign';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $senderApiClient = DocuSign::getApiClientForUser($this->assignment->travel->primaryContact);

        if ($senderApiClient === null) {
            $this->fail('Could not send envelope because primary contact does not have valid DocuSign credentials');

            return;
        }

        $assignment = $this->assignment;

        if (! $assignment->user->has_emergency_contact_information) {
            $this->fail('Could not send envelope because traveler does not have emergency contact information');

            return;
        }

        if ($assignment->user->phone === null) {
            $this->fail('Could not send envelope because traveler does not have phone number');

            return;
        }

        if ($assignment->user->docusign_access_token === null) {
            $this->fail('Could not send envelope because traveler does not have DocuSign account');

            return;
        }

        Cache::lock(name: $this->assignment->user->uid.'_docusign', seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($senderApiClient, $assignment) {
                $envelopesApi = new EnvelopesApi($senderApiClient);

                if ($assignment->envelope()->whereNotNull('envelope_id')->count() === 0) {
                    $envelope = new DocuSignEnvelope();
                    $envelope->signed_by = $assignment->user->id;
                    $envelope->signable_type = $assignment->getMorphClass();
                    $envelope->signable_id = $assignment->id;
                    $envelope->sent_by = $assignment->travel->primaryContact->id;
                    $envelope->save();

                    $envelopeResponse = $envelopesApi->createEnvelope(
                        account_id: config('docusign.account_id'),
                        envelope_definition: DocuSign::travelAssignmentEnvelopeDefinition($envelope)
                    );

                    $envelope->envelope_id = $envelopeResponse->getEnvelopeId();
                    $envelope->save();
                }
            }
        );
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->assignment->id);
    }
}
