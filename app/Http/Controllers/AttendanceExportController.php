<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Attendance;
use App\AttendanceExport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceExportController extends Controller
{
    /**
     * Generate and download an attendance report.
     *
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(Request $request, string $secret)
    {
        $export = AttendanceExport::where('secret', $secret)->first();

        if (null === $export || $export->expires_at < Carbon::now('America/New_York')) {
            return view(
                'attendance.export',
                [
                    'message' => 'That link is no longer valid.',
                ]
            );
        } else if (null !== $export->downloaded_at) {
            return view(
                'attendance.export',
                [
                    'message' => 'That link has already been used.'.
                ]
            );
        }

        $export->downloaded_at = Carbon::now('America/New_York');
        $export->downloadedUser = $request->user();
        $export->save();

        return response()->streamDownload(static function () with ($export): void {
            $attendance = Attendance::whereBetween('created_at', [$export->start_time, $export->end_time])->get();

            echo Attendance::formatAsCSV($attendance);
        }, 'RoboJacketsAttendance.csv');
    }
}
