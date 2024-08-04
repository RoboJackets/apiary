<?php

declare(strict_types=1);

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

class PruneTravelAssignmentNotificationsInNova implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user)
    {
    }

    /**
     * Execute the job.
     *
     * @phan-suppress PhanPluginNonBoolBranch
     */
    public function handle(): void
    {
        if ($this->user->assignments->reduce(
            static fn (bool $carry, TravelAssignment $assignment): bool => $carry && $assignment->is_complete,
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

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return [
            'user:'.$this->user->uid,
        ];
    }
}
