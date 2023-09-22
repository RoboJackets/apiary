<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DuesPackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminders implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user)
    {
        $this->queue = 'email';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // check if travel assignment reminder should be sent
        if ($this->user->assignments()->needDocuSign()->exists()) {
            SendTravelAssignmentReminder::dispatch(
                $this->user->assignments()->needDocuSign()->first(),
                24
            );

            return;
        }

        if ($this->user->assignments()->unpaid()->exists()) {
            SendTravelAssignmentReminder::dispatch(
                $this->user->assignments()->unpaid()->first(),
                24
            );

            return;
        }

        if ($this->user->primary_affiliation !== 'student') {
            return;
        }

        // check if dues transaction reminder should be sent
        if (! $this->user->is_active &&
            $this->user->duesTransactions()->pending()->doesntExist() &&
            DuesPackage::userCanPurchase($this->user)->exists() &&
            DuesPackage::whereDate('effective_end', '<', Carbon::now())
                ->whereDate('access_end', '>', Carbon::now())
                ->doesntExist() &&
            $this->user->attendance()->exists()
        ) {
            SendDuesTransactionReminder::dispatch($this->user);

            return;
        }

        // check if dues payment reminder should be sent
        if (! $this->user->is_active &&
            DuesPackage::userCanPurchase($this->user)->exists() &&
            $this->user->duesTransactions()->pending()->exists()
        ) {
            SendDuesPaymentReminder::dispatch($this->user, 24);
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
