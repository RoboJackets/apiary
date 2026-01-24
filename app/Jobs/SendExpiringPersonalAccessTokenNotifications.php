<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Notifications\ExpiringPersonalAccessTokenNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Passport\Passport;

class SendExpiringPersonalAccessTokenNotifications implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recently_expired = Carbon::now()->subWeek();
        $expiring_soon = Carbon::now()->addWeeks(2);

        $pats = Passport::token()
            ->whereDate('expires_at', '>=', $recently_expired)
            ->whereDate('expires_at', '<', $expiring_soon)
            ->whereRevoked(false)
            ->whereHas('client', static function (Builder $query): void {
                $query->whereJsonContains('grant_types', 'personal_access');
            })
            ->whereHas('user')
            ->get();

        foreach ($pats as $pat) {
            $owner = $pat->user()->sole();

            $owner->notify(
                (new ExpiringPersonalAccessTokenNotification($pat))
                    ->delay(now()->addMinutes(random_int(10, 50)))
            );
        }
    }
}
