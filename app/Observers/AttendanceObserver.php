<?php

declare(strict_types=1);

namespace App\Observers;

use App\Attendance;
use App\Jobs\PushToJedi;

class AttendanceObserver
{
    public function saved(Attendance $attendance): void
    {
        if (null === $attendance->attendee) {
            return;
        }

        PushToJedi::dispatch($attendance->attendee, Attendance::class, $attendance->id, 'saved')->onQueue('jedi');
    }
}
