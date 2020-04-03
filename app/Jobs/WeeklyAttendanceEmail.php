<?php

declare(strict_types=1);

namespace App\Jobs;

use App\AttendanceExport;
use App\Mail\Attendance\Report;
use App\Notifiables\CoreOfficersNotifiable;
use App\Notifications\WeeklyAttendanceEmailConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class WeeklyAttendanceEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (null === config('services.attendance_email')) {
            return;
        }

        $export = new AttendanceExport();

        // Today is Sunday, so go back 7 days to last Sunday at the start of the day.
        // Stop at yesterday at the end of the day.
        $export->start_time = Carbon::now()->subDays(7)->startOfDay();
        $export->end_time = Carbon::now()->subDays(1)->endOfDay();

        if (0 === Attendance::whereBetween('created_at', [$export->start_time, $export->end_time])->count()) {
            // No attendance? Don't bother emailing.
            (new CoreOfficersNotifiable())->notify(new WeeklyAttendanceEmailConfirmation($export, true));
            return;
        }

        $export->expires_at = Carbon::now()->addDays(3);
        $export->secret = hash('sha256', random_bytes(64));

        $export->save();

        Mail::to(config('services.attendance_email'))->send(new Report($export));

        (new CoreOfficersNotifiable())->notify(new WeeklyAttendanceEmailConfirmation($export));
    }
}
