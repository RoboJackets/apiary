<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\RemoteAttendanceLink;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RemoteAttendanceController
{
    private static function handleRequest(Request $request, string $secret, bool $redirect)
    {
        $link = RemoteAttendanceLink::where('secret', $secret)->first();

        if ($link === null) {
            return view(
                'attendance.remote',
                [
                    'message' => 'That link is invalid. Ask your project manager for a new one.',
                ]
            );
        }

        $expired = $link->expires_at < Carbon::now('America/New_York');
        // Sanity check that the URL is actually a URL before we show it to the user as a link
        $urlIsValid = $link->redirect_url !== null && Validator::make([
            'redirect_url' => $link->redirect_url,
        ], [
            'redirect_url' => 'url',
        ])->passes();

        if ($expired) {
            if ($urlIsValid) {
                return view(
                    'attendance.remote',
                    [
                        'message' => 'That link is no longer valid, so your attendance has not been recorded. '.
                            'Ask your project manager for a new one. However, you can still continue to the meeting:',
                        'linkDestination' => $link->redirect_url,
                    ]
                );
            }

            return view(
                'attendance.remote',
                [
                    'message' => 'That link is no longer valid, so your attendance has not been recorded. '.
                        'Ask your project manager for a new one.',
                ]
            );
        }

        $attendable_type = $link->attendable_type;
        $attendable_id = $link->attendable_id;
        $gtid = $request->user()->gtid;

        $attExists = Attendance::where('attendable_type', $attendable_type)
            ->where('attendable_id', $attendable_id)
            ->where('gtid', $gtid)
            ->whereDate('created_at', date('Y-m-d'))->exists();

        if (! $attExists) {
            $att = new Attendance();
            $att->attendable_type = $attendable_type;
            $att->attendable_id = $attendable_id;
            $att->gtid = $gtid;
            $att->source = 'remote-attendance-link';
            $att->recorded_by = $request->user()->id;
            $att->remote_attendance_link_id = $link->id;
            $att->save();
        }

        $name = is_a($link->attendable, Team::class) || is_a($link->attendable, Event::class)
            ? $link->attendable->name
            : 'this event';

        if (! $urlIsValid) {
            return view(
                'attendance.remote',
                [
                    'message' => 'Your attendance has been recorded for '.$name.'. You may now close this page.',
                ]
            );
        }

        if ($redirect) {
            return redirect()->away($link->redirect_url);
        }

        return view(
            'attendance.remote',
            [
                'message' => 'Your attendance has been recorded for '.$name.'. You can now continue to the meeting:',
                'linkDestination' => $link->redirect_url,
            ]
        );
    }

    public function index(Request $request, string $secret)
    {
        return self::handleRequest($request, $secret, false);
    }

    public function redirect(Request $request, string $secret)
    {
        return self::handleRequest($request, $secret, true);
    }
}
