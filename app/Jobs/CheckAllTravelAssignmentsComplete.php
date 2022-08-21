<?php

declare(strict_types=1);

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

    /**
     * Create a new job instance.
     */
    public function __construct(public Travel $travel)
    {
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
                        static fn (bool $carry, TravelAssignment $each): bool => $carry && $each->is_complete,
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
