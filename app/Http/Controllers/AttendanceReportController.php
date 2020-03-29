<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class AttendanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-attendance', ['only' => ['show']]);
    }

    /**
     * Download a pre-saved attendance report.
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(string $hash)
    {
        if (! ctype_alnum($hash)) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'invalid_datecode',
                ],
                400
            );
        }

        return response()->download(
            __DIR__.'/../../../storage/app/attendance-reports/'.$hash.'.csv',
            'RoboJacketsAttendance.csv'
        )->deleteFileAfterSend(true);
    }
}
