<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CheckAllTravelAssignmentsComplete;
use App\Jobs\PruneTravelAssignmentNotificationsInNova;
use App\Jobs\SendDocuSignEnvelopeForTravelAssignment;
use App\Jobs\SendTravelAssignmentCreatedNotification;
use App\Jobs\SendTravelAssignmentReminder;
use App\Models\TravelAssignment;
use App\Notifications\Travel\TravelAssignmentCreated;

class TravelAssignmentObserver
{
    public function created(TravelAssignment $assignment): void
    {
        if ($assignment->travel->needs_docusign === true) {
            SendDocuSignEnvelopeForTravelAssignment::dispatch($assignment)
                ->chain([
                    new SendTravelAssignmentCreatedNotification($assignment),
                ]);
        } else {
            $assignment->user->notify(new TravelAssignmentCreated($assignment));
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
