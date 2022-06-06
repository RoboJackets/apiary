<?php

declare(strict_types=1);

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
