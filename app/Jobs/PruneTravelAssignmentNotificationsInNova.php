<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\TravelAssignment;
use App\Models\User;
use App\Notifications\Travel\TravelAssignmentCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PruneTravelAssignmentNotificationsInNova implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @phan-suppress PhanPluginNonBoolBranch
     *
     * @return void
     */
    public function handle()
    {
        if ($this->user->assignments->reduce(
            static function (bool $carry, TravelAssignment $assignment): bool {
                return $carry && $assignment->is_complete;
            },
            true
        )) {
            $this->user->novaNotifications()
                       ->where('type', TravelAssignmentCreated::class)
                       ->delete();
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->user->id);
    }
}
