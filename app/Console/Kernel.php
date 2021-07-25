<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\DailyDuesSummary;
use App\Jobs\NoAttendanceJediPush;
use App\Jobs\SendExpiringPersonalAccessTokenNotifications;
use App\Jobs\WeeklyAttendanceEmail;
use App\Jobs\WeeklyAttendanceSlack;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use UKFast\HealthCheck\Commands\CacheSchedulerRunning;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<string>
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command(CacheSchedulerRunning::class)->everyMinute();
        $schedule->command('passport:purge')->twiceDaily();

        $schedule->job(new WeeklyAttendanceEmail())->weekly()->sundays()->at('1:00');
        $schedule->job(new WeeklyAttendanceSlack())->weekly()->sundays()->at('11:00');
        $schedule->job(new SendExpiringPersonalAccessTokenNotifications())->weekly()->mondays()->at('08:00');
        $schedule->job(new DailyDuesSummary())->daily()->at('11:00');
        $schedule->job(new NoAttendanceJediPush())->daily()->at('10:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        include base_path('routes/console.php');
    }
}
