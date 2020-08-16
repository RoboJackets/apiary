<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Notifiables\TreasurerNotifiable;
use App\Notifications\Dues\SummaryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DailyDuesSummary implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new TreasurerNotifiable())->notify(new SummaryNotification());
    }
}
