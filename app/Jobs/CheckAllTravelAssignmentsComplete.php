<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Notifications\Travel\AllTravelAssignmentsComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

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
        $travel = $this->travel;
        Cache::lock('send_completion_email_'.$travel->id, 5 /* seconds */)->get(
            static function () use ($travel): void {
                if (! $travel->completion_email_sent &&
                    $travel->assignments->reduce(
                        static function (bool $carry, TravelAssignment $assignment): bool {
                            return $carry && $assignment->is_complete;
                        },
                        true
                    )
                ) {
                    $travel->completion_email_sent = true;
                    $travel->save();

                    $travel->primaryContact->notify(new AllTravelAssignmentsComplete($travel));
                }
            }
        );
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->travel->id);
    }
}
