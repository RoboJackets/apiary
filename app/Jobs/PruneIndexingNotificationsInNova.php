<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Notifications\Nova\IndexingInProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Notifications\Notification;

class PruneIndexingNotificationsInNova implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'meilisearch';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::where('type', IndexingInProgress::class)
            ->delete();
    }
}
