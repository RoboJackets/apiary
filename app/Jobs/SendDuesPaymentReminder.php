<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\User;
use App\Notifications\DuesPaymentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDuesPaymentReminder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->queue = 'email';
        $this->delay = now()->addHours(48)->hour(10)->startOfHour()->addMinutes(random_int(10, 50));

        if (5 === $this->delay->dayOfWeek) {
            // do not send reminders on thursdays to reduce the chance of user
            // trying to use the app during a maintenance window
            $this->delay = $this->delay->addHours(24);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transaction = $this->user->dues()->unpaid()->orderByDesc('updated_at')->first();

        if (null === $transaction) {
            return;
        }

        $this->user->notify(new DuesPaymentReminder($transaction));
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->user->id);
    }
}
