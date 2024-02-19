<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PruneAccessFromAccessInactiveUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'jedi';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::accessInactive(CarbonImmutable::now()->subDay())
            ->where('is_service_account', '=', false)
            ->where(static function (Builder $query): void {
                $query->whereHas('roles')
                    ->orWhereHas('permissions');
            })
            ->get()
            ->filter(static fn (User $user): bool => ! $user->is_access_active)
            ->each(static function (User $user): void {
                $user->roles()->detach();
                $user->permissions()->detach();

                $user->manages()
                    ->update(['project_manager_id' => null]);

                $user->novaNotifications()
                    ->delete();

                $user->searchable();

                PushToJedi::dispatch($user, self::class, -1, 'deactivation');
            });
    }
}
