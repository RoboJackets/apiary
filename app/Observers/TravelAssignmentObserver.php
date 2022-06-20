<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CheckAllTravelAssignmentsComplete;
use App\Jobs\PruneTravelAssignmentNotificationsInNova;
use App\Jobs\SendTravelAssignmentReminder;
use App\Models\TravelAssignment;
use App\Notifications\Travel\TravelAssignmentCreated;

class TravelAssignmentObserver
{
    public function created(TravelAssignment $assignment): void
    {
        $assignment->user->notify(new TravelAssignmentCreated($assignment));
        SendTravelAssignmentReminder::dispatch($assignment);
    }

    public function saved(TravelAssignment $assignment): void
    {
        SendTravelAssignmentReminder::dispatch($assignment);
        PruneTravelAssignmentNotificationsInNova::dispatch($assignment->user);
        CheckAllTravelAssignmentsComplete::dispatch($assignment->travel);
    }
}
