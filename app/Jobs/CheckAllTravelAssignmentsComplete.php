<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments

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

class CheckAllTravelAssignmentsComplete implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Travel $travel)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $travel = $this->travel;
        Cache::lock(name: 'send_completion_email_'.$travel->id, seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($travel): void {
                if (! $travel->payment_completion_email_sent && ! $travel->assignments_need_payment) {
                    $travel->payment_completion_email_sent = true;
                    $travel->form_completion_email_sent = ! $travel->assignments_need_forms;
                    $travel->save();
                    $travel->primaryContact->notify(new AllTravelAssignmentsComplete($travel));

                    $travel->assignments()->needDocuSign()->get()->each(
                        static function (TravelAssignment $assignment): void {
                            SendTravelAssignmentReminder::dispatch($assignment);
                        }
                    );
                } elseif ($travel->tar_required &&
                    ! $travel->form_completion_email_sent &&
                    ! $travel->assignments_need_forms
                ) {
                    $travel->payment_completion_email_sent = ! $travel->assignments_need_payment;
                    $travel->form_completion_email_sent = true;
                    $travel->save();
                    $travel->primaryContact->notify(new AllTravelAssignmentsComplete($travel));

                    $travel->assignments()->unpaid()->get()->each(
                        static function (TravelAssignment $assignment): void {
                            SendTravelAssignmentReminder::dispatch($assignment);
                        }
                    );
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
