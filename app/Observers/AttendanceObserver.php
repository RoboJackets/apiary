<?php

declare(strict_types=1);

namespace App\Observers;

use App\Attendance;
use App\Jobs\CreateUserFromBuzzAPI;
use App\Jobs\PushToJedi;

class AttendanceObserver
{
    public function saved(Attendance $attendance): void
    {
        if (null === $attendance->attendee) {
            // I know this will not cause a PushToJedi run, but if the user is being created from attendance they will
            // not have access to anything with Jedi anyway.
            CreateUserFromBuzzAPI::dispatch(CreateUserFromBuzzAPI::IDENTIFIER_GTID, $attendance->gtid)
                ->onQueue('buzzapi');

            return;
        }

        PushToJedi::dispatch($attendance->attendee, Attendance::class, $attendance->id, 'saved')->onQueue('jedi');
    }
}
