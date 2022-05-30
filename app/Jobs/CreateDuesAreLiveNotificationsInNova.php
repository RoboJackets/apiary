<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Jobs;

use App\Models\DuesPackage;
use App\Models\User;
use App\Notifications\Nova\DuesAreLive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateDuesAreLiveNotificationsInNova implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::permission('access-nova')
            ->inactive()
            ->whereNotExists(static function (Builder $query): void {
                $query->select(DB::raw(1))
                    ->from('nova_notifications')
                    ->where('notifiable_type', '=', User::class)
                    ->whereRaw('notifiable_id = users.id')
                    ->where('type', DuesAreLive::class)
                    ->where('created_at', '>', now()->subMonths(3));
            })
            ->get()
            ->each(static function (User $user): void {
                if (DuesPackage::userCanPurchase($user)->count() > 0) {
                    $user->notify(new DuesAreLive($user));
                }
            });
    }
}
