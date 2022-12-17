<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendReminders;
use App\Models\DocuSignEnvelope;
use App\Models\TravelAssignment;

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
        }
    }

    public function saved(DocuSignEnvelope $envelope): void
    {
        SendReminders::dispatch($envelope->signedBy);
    }
}
