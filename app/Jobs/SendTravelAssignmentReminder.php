<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\TravelAssignment;
use App\Notifications\Travel\TravelAssignmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTravelAssignmentReminder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public TravelAssignment $assignment;

    /**
     * Create a new job instance.
     */
    public function __construct(TravelAssignment $assignment)
    {
        $this->assignment = $assignment;
        $this->queue = 'email';
        $this->delay = now()->addHours(48)->hour(10)->startOfHour()->addMinutes(random_int(10, 50));

        if (5 === $this->delay->dayOfWeek) {
            // do not send reminders on thursdays to reduce the chance of user
            // trying to use the app during a maintenance window
            $this->delay = $this->delay->addHours(24);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->assignment->user->notify(new TravelAssignmentReminder($this->assignment));
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->assignment->user->id);
    }
}
