<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class NoAttendanceJediPush implements ShouldQueue
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
        $users = User::accessActive()->whereDoesntHave('attendance', static function (Builder $query): void {
            $query->where('attendable_type', Team::getMorphClassStatic())->where(
                'created_at',
                '>',
                (new Carbon(config('sums.attendance_timeout_limit'), 'America/New_York'))->startOfDay()->addDays(1)
            );
        })->whereHas('attendance', static function (Builder $query): void {
            $query->where('attendable_type', Team::getMorphClassStatic())->whereBetween('created_at', [
                (new Carbon(config('sums.attendance_timeout_limit'), 'America/New_York'))->startOfDay(),
                (new Carbon(config('sums.attendance_timeout_limit'), 'America/New_York'))->endOfDay(),
            ]);
        })->get();

        foreach ($users as $user) {
            PushToJedi::dispatch($user, self::class, -1, 'no attendance');
        }
    }
}
