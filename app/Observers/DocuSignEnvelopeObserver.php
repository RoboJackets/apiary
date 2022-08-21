<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DocuSignEnvelope;
use App\Models\TravelAssignment;

class DocuSignEnvelopeObserver
{
    /**
     * Handle the DocuSignEnvelope "deleted" event.
     */
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

    /**
     * Handle the DocuSignEnvelope "restored" event.
     */
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

    /**
     * Handle the DocuSignEnvelope "force deleted" event.
     */
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
}
