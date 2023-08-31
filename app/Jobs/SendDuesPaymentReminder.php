<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Dues\PaymentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDuesPaymentReminder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly User $user, int $delay = 48)
    {
        $this->queue = 'email';
        $this->delay = now()->addHours($delay)->hour(10)->startOfHour()->addMinutes(random_int(10, 50));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transaction = $this->user->dues()->unpaid()->orderByDesc('updated_at')->first();

        if ($transaction === null) {
            return;
        }

        $this->user->notify(new PaymentReminder($transaction));
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
