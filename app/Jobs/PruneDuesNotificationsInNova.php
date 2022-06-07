<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

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

class PruneDuesNotificationsInNova implements ShouldQueue, ShouldBeUnique
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
     * @return void
     */
    public function handle()
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
}
