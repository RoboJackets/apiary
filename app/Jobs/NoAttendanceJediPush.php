<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Team;
use App\User;
use Illuminate\Bus\Queueable;
use App\Jobs\NoAttendanceJediPush;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NoAttendanceJediPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $users = User::active()->whereDoesntHave('attendance', function (Builder $query) {
            $query->where('attendable_type', Team::class)->where('created_at', '>', now()->startOfDay()->subDays(28));
        });
        foreach ($users as $user) {
            PushToJedi::dispatch($user, NoAttendanceJediPush::class, -1, 'no_attendance')->onQueue('jedi');
        }
    }
}
