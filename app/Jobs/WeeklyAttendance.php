<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Team;
use Illuminate\Bus\Queueable;
use App\Notifiables\CoreNotifiable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\TeamAttendanceNotification;
use App\Notifications\GlobalAttendanceNotification;

class WeeklyAttendance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach (Team::where('attendable', 1)->get() as $team) {
            $team->notify(new TeamAttendanceNotification());
        }

        (new CoreNotifiable())->notify(new GlobalAttendanceNotification());
    }
}
