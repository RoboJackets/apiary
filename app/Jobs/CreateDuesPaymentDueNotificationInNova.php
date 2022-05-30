<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Nova\DuesPaymentDue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDuesPaymentDueNotificationInNova implements ShouldQueue, ShouldBeUnique
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
        if ($this->user->dues()->pending()->count() > 0 &&
            $this->user->hasPermissionTo('access-nova') &&
            0 === $this->user->novaNotifications()
                             ->where('type', DuesPaymentDue::class)
                             ->where('created_at', '>', now()->subMonths(3))
                             ->count()
        ) {
            $this->user->notify(new DuesPaymentDue($this->user));
        }
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return strval($this->user->id);
    }
}
