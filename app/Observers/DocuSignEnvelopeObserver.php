<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments

namespace App\Observers;

use App\Jobs\PushToJedi;
use App\Jobs\SendReminders;
use App\Models\DocuSignEnvelope;
use App\Models\Signature;
use App\Models\TravelAssignment;
use App\Notifications\MembershipAgreementDocuSignEnvelopeReceived;
use App\Notifications\Travel\DocuSignEnvelopeReceived;
use Illuminate\Support\Facades\Cache;

class DocuSignEnvelopeObserver
{
    public function deleted(DocuSignEnvelope $envelope): void
    {
        if (
            $envelope->signable_type === TravelAssignment::getMorphClassStatic() &&
            $envelope->signable->envelope->count() === 0
        ) {
            $envelope->signable->tar_received = false;
            $envelope->signable->save();
        } elseif (
            $envelope->signable_type === Signature::getMorphClassStatic() &&
            $envelope->signable->envelope->count() === 0
        ) {
            $envelope->signable->complete = false;
            $envelope->signable->save();
        }
    }

    public function restored(DocuSignEnvelope $envelope): void
    {
        if (
            $envelope->signable_type === TravelAssignment::getMorphClassStatic() &&
            $envelope->signable->envelope->count() === 1
        ) {
            $envelope->signable->tar_received = $envelope->complete;
            $envelope->signable->save();
        } elseif (
            $envelope->signable_type === Signature::getMorphClassStatic() &&
            $envelope->signable->envelope->count() === 1
        ) {
            $envelope->signable->complete = $envelope->complete;
            $envelope->signable->save();
        }
    }

    public function forceDeleted(DocuSignEnvelope $envelope): void
    {
        if (
            $envelope->signable_type === TravelAssignment::getMorphClassStatic() &&
            $envelope->signable->envelope->count() === 0
        ) {
            $envelope->signable->tar_received = false;
            $envelope->signable->save();
        } elseif (
            $envelope->signable_type === Signature::getMorphClassStatic() &&
            $envelope->signable->envelope->count() === 0
        ) {
            $envelope->signable->complete = false;
            $envelope->signable->save();
        }
    }

    public function saved(DocuSignEnvelope $envelope): void
    {
        SendReminders::dispatch($envelope->signedBy);
        PushToJedi::dispatch($envelope->signedBy, DocuSignEnvelope::class, $envelope->id, 'saved');

        if (! $envelope->acknowledgement_sent) {
            Cache::lock(name: 'send_acknowledgement_'.$envelope->id, seconds: 120)->block(
                seconds: 60,
                callback: static function () use ($envelope): void {
                    $envelope->refresh();
                    if (
                        $envelope->complete &&
                        $envelope->envelope_id !== null &&
                        $envelope->signable_type === Signature::getMorphClassStatic() &&
                        ! $envelope->acknowledgement_sent // @phpstan-ignore-line
                    ) {
                        $envelope->acknowledgement_sent = true;
                        $envelope->save();

                        $envelope->signedBy->notify(new MembershipAgreementDocuSignEnvelopeReceived($envelope));
                    } elseif (
                        $envelope->complete &&
                        $envelope->envelope_id !== null &&
                        $envelope->signable_type === TravelAssignment::getMorphClassStatic() &&
                        ! $envelope->acknowledgement_sent // @phpstan-ignore-line
                    ) {
                        $envelope->acknowledgement_sent = true;
                        $envelope->save();

                        $envelope->signedBy->notify(new DocuSignEnvelopeReceived($envelope));
                    }
                }
            );
        }

        if ($envelope->complete && $envelope->signable_type === Signature::getMorphClassStatic()) {
            $envelope->signable->complete = true;
            $envelope->signable->save();
        } elseif ($envelope->complete && $envelope->signable_type === TravelAssignment::getMorphClassStatic()) {
            $envelope->signable->tar_received = true;
            $envelope->signable->save();
        }
    }
}
