<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RemoteAttendanceController extends Controller
{
    public function index(Request $request, string $secret)
    {
        $team = Team::where('attendance_secret', $secret)->first();

        if (null === $team
            // @phan-suppress-next-line PhanTypeExpectedObjectPropAccessButGotNull
            || $team->attendance_secret_expiration < Carbon::now('America/New_York')
        ) {
            return view(
                'attendance.remote',
                [
                    'message' => 'That link is no longer valid. Ask your project manager for a new one.',
                ]
            );
        }

        $attendable_type = Team::class;
        $attendable_id = $team->id;
        $gtid = $request->user()->gtid;

        $attExisting = Attendance::where('attendable_type', $attendable_type)
            ->where('attendable_id', $attendable_id)
            ->where('gtid', $gtid)
            ->whereDate('created_at', date('Y-m-d'))->count();

        if (0 === $attExisting) {
            $att = new Attendance();
            $att->attendable_type = $attendable_type;
            $att->attendable_id = $attendable_id;
            $att->gtid = $gtid;
            $att->source = 'secret-link';
            $att->recorded_by = $request->user()->id;
            $att->save();
        }

        return view(
            'attendance.remote',
            [
                'message' => 'Your attendance has been recorded for '.$team->name
                .'. You may now close this page.',
            ]
        );
    }
}
