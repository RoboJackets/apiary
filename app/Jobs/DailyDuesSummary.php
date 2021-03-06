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
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'slack';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new TreasurerNotifiable())->notify(new SummaryNotification());
    }
}
