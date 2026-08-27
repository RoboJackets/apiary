<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use App\Jobs\PushToJedi;
use App\Jobs\SendReminders;
use App\Models\Attendance;

class AttendanceObserver
{
    public function saved(Attendance $attendance): void
    {
        if ($attendance->attendee === null && $attendance->gtid !== null) {
            // I know this will not cause a PushToJedi run, but if the user is being created from attendance they will
            // not have access to anything with Jedi anyway.
            CreateOrUpdateUserFromBuzzAPI::dispatch(
                CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_GTID,
                $attendance->gtid,
                'attendance'
            );

            return;
        }

        if ($attendance->attendee !== null) {
            CreateOrUpdateUserFromBuzzAPI::dispatch(
                CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER,
                $attendance->attendee,
                'attendance'
            );

            PushToJedi::dispatch($attendance->attendee, Attendance::class, $attendance->id, 'saved');

            $attendance->attendee->searchable();

            SendReminders::dispatch($attendance->attendee);
        }
    }
}
