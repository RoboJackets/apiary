<?php

namespace App\Jobs;

use App\Notifications\ExpiringPersonalAccessTokenNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Passport\Passport;

class SendExpiringPersonalAccessTokenNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recently_expired = Carbon::now()->subWeek();
        $expiring_soon = Carbon::now()->addWeek();

        $pats = Passport::token()
            ->whereDate('expires_at', '>=', $recently_expired)
            ->whereDate('expires_at', '<', $expiring_soon)
            ->whereHas('client', function ($clientQuery) {
                $clientQuery->where('user_id', '=', null); // PATs are created with a Personal Access Client that isn't associated with any user
            })->get();

        foreach ($pats as $pat) {
            $owner = $pat->user()->first();

            $owner->notify(new ExpiringPersonalAccessTokenNotification($pat));
        }
    }
}
