<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

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
            0 === $envelope->signable->envelope->count()
        ) {
            $envelope->signable->tar_received = false;
            $envelope->signable->save();
        } else {
            throw new \Exception('Unrecognized signable_type '.$envelope->signable_type);
        }
    }

    /**
     * Handle the DocuSignEnvelope "restored" event.
     */
    public function restored(DocuSignEnvelope $envelope): void
    {
        if (
            $envelope->signable_type === TravelAssignment::getMorphClassStatic() &&
            0 === $envelope->signable->envelope->count()
        ) {
            $envelope->signable->tar_received = $envelope->complete;
            $envelope->signable->save();
        } else {
            throw new \Exception('Unrecognized signable_type '.$envelope->signable_type);
        }
    }

    /**
     * Handle the DocuSignEnvelope "force deleted" event.
     */
    public function forceDeleted(DocuSignEnvelope $envelope): void
    {
        if (
            $envelope->signable_type === TravelAssignment::getMorphClassStatic() &&
            0 === $envelope->signable->envelope->count()
        ) {
            $envelope->signable->tar_received = false;
            $envelope->signable->save();
        } else {
            throw new \Exception('Unrecognized signable_type '.$envelope->signable_type);
        }
    }
}
