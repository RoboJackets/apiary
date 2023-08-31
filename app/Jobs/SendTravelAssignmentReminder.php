<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TravelAssignment;
use App\Notifications\Travel\TravelAssignmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTravelAssignmentReminder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly TravelAssignment $assignment, int $delay = 48)
    {
        $this->queue = 'email';
        $this->delay = now()->addHours($delay)->hour(10)->startOfHour()->addMinutes(random_int(10, 50));
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
