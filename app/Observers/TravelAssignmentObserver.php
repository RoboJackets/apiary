<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CheckAllTravelAssignmentsComplete;
use App\Jobs\PruneTravelAssignmentNotificationsInNova;
use App\Jobs\SendDocuSignEnvelopeForTravelAssignment;
use App\Jobs\SendTravelAssignmentCreatedNotification;
use App\Jobs\SendTravelAssignmentReminder;
use App\Models\TravelAssignment;

class TravelAssignmentObserver
{
    public function created(TravelAssignment $assignment): void
    {
        if ($assignment->travel->tar_required === true || $assignment->travel->needs_airfare_form) {
            SendDocuSignEnvelopeForTravelAssignment::dispatch($assignment)
                ->chain([
                    new SendTravelAssignmentCreatedNotification($assignment),
                ]);
        }

        SendTravelAssignmentReminder::dispatch($assignment);
    }

    public function saved(TravelAssignment $assignment): void
    {
        SendTravelAssignmentReminder::dispatch($assignment);
        PruneTravelAssignmentNotificationsInNova::dispatch($assignment->user);
        CheckAllTravelAssignmentsComplete::dispatch($assignment->travel);
    }
}
