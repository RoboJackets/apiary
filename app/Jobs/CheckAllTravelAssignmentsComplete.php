<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\Travel;
use App\Notifications\Travel\AllTravelAssignmentsComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAllTravelAssignmentsComplete implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public Travel $travel;

    /**
     * Create a new job instance.
     */
    public function __construct(Travel $travel)
    {
        $this->travel = $travel;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->travel->primaryContact->notify(new AllTravelAssignmentsComplete($this->travel));
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->travel->id);
    }
}
