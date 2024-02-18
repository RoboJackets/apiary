<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition.RequiredSingleLineCondition

namespace App\Jobs;

use App\Mail\Dues\PackageExpirationReminder;
use App\Models\DuesPackage;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRemindersForExpiringDuesPackages implements ShouldQueue
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
        if (
            DuesPackage::whereDate('access_end', '>', Carbon::now())
                ->whereDate('access_end', '<', Carbon::now()->addDays(7))
                ->exists()
        ) {
            Mail::send(new PackageExpirationReminder());
        }
    }
}
