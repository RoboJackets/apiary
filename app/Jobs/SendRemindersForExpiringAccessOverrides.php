<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRemindersForExpiringAccessOverrides implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'email';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::whereDate('access_override_until', '>', Carbon::now())
            ->whereDate('access_override_until', '<', Carbon::now()->addDays(2))
            ->get()
            ->each(static function (User $user): void {
                SendReminders::dispatch($user);
            });
    }
}
