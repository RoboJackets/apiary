<?php

declare(strict_types=1);

use App\Jobs\DailyDuesSummary;
use App\Jobs\NoAttendanceJediPush;
use App\Jobs\PruneAccessFromAccessInactiveUsers;
use App\Jobs\SendExpiringPersonalAccessTokenNotifications;
use App\Jobs\SendRemindersForExpiringAccessOverrides;
use App\Jobs\SendRemindersForExpiringDuesPackages;
use App\Jobs\WeeklyAttendanceSlack;
use Illuminate\Support\Facades\Schedule;
use UKFast\HealthCheck\Commands\CacheSchedulerRunning;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command(CacheSchedulerRunning::class)->everyMinute();
Schedule::command('passport:purge')->twiceDaily();

Schedule::job(new WeeklyAttendanceSlack())->weekly()->sundays()->at('11:00');
Schedule::job(new SendExpiringPersonalAccessTokenNotifications())->weekly()->mondays()->at('08:00');
Schedule::job(new DailyDuesSummary())->daily()->at('00:00');
Schedule::job(new NoAttendanceJediPush())->daily()->at('10:00');
Schedule::job(new SendRemindersForExpiringAccessOverrides())->daily()->at('04:00');
Schedule::job(new SendRemindersForExpiringDuesPackages())->daily()->at('08:00');

if (config('features.prune-access') === true) {
    Schedule::job(new PruneAccessFromAccessInactiveUsers())->daily()->at('04:00');
}
