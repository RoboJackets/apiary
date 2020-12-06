<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceExportController extends Controller
{
    /**
     * Generate and download an attendance report.
     *
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(Request $request, string $secret)
    {
        $export = AttendanceExport::where('secret', $secret)->first();

        // @phan-suppress-next-line PhanTypeExpectedObjectPropAccessButGotNull
        if (null === $export || $export->expires_at < Carbon::now('America/New_York')) {
            return view(
                'attendance.export',
                [
                    'message' => 'That link is no longer valid.',
                ]
            );
        }
        if (null !== $export->downloaded_at) {
            return view(
                'attendance.export',
                [
                    'message' => 'That link has already been used.',
                ]
            );
        }

        $export->downloaded_at = Carbon::now('America/New_York');
        $export->downloadUser()->associate($request->user());
        $export->save();

        return response()->streamDownload(static function () use ($export): void {
            $attendance = Attendance::whereBetween('created_at', [$export->start_time, $export->end_time])->get();

            echo Attendance::formatAsCsv($attendance);
        }, 'RoboJacketsAttendance.csv');
    }
}
