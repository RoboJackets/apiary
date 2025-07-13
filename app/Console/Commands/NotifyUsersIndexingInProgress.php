<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Nova\IndexingInProgress;
use Illuminate\Console\Command;

/**
 * Notify users indexing is in progress.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class NotifyUsersIndexingInProgress extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify-indexing-in-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Nova notifications to indicate indexing is in progress';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        User::permission('access-nova')
            ->get()
            ->each(static function (User $user): void {
                $user->notify(new IndexingInProgress());
            });

        return 0;
    }
}
