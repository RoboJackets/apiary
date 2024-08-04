<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Team;
use App\Notifiables\CoreNotifiable;
use App\Notifications\GlobalAttendanceNotification;
use App\Notifications\TeamAttendanceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WeeklyAttendanceSlack implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'slack';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach (Team::where('attendable', true)->get() as $team) {
            $team->notify(new TeamAttendanceNotification());
        }

        (new CoreNotifiable())->notify(new GlobalAttendanceNotification());
    }
}
