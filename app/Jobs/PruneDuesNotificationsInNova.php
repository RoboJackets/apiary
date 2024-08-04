<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Nova\DuesAreLive;
use App\Notifications\Nova\DuesPaymentDue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PruneDuesNotificationsInNova implements ShouldBeUnique, ShouldQueue
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
     */
    public function handle(): void
    {
        if ($this->user->dues()->pending()->count() > 0 || $this->user->is_active) {
            $this->user->novaNotifications()
                ->where('type', DuesAreLive::class)
                ->delete();
        }

        if ($this->user->is_active) {
            $this->user->novaNotifications()
                ->where('type', DuesPaymentDue::class)
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
