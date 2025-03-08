<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SUMSController
{
    /**
     * Returns view for SUMS status.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->signed_latest_agreement && config('sums.requires_agreement') === true) {
            return view(
                'sums',
                [
                    'message' => 'You need to sign the latest membership agreement to gain access to SUMS.',
                ]
            );
        }

        if (! $user->is_access_active) {
            return view(
                'sums',
                [
                    'message' => 'You have not paid dues, so you do not have access to SUMS right now.',
                ]
            );
        }

        if (count($user->teams) === 0) {
            return view(
                'sums',
                [
                    'message' => 'You are not a member of any teams yet. Join a team first, then try again.',
                ]
            );
        }

        $lastAttendance = $user->attendance()->where('attendable_type', Team::getMorphClassStatic())
            ->whereNull('remote_attendance_link_id')
            ->whereNull('people_counter_id')
            ->orderByDesc('created_at')->first();

        if ($lastAttendance !== null
            && $lastAttendance->created_at < new Carbon(config('sums.attendance_timeout_limit'), 'America/New_York')
        ) {
            return view(
                'sums',
                [
                    'message' => 'You have not been to the shop recently, so you do not have access to SUMS right now.',
                ]
            );
        }

        if ($user->exists_in_sums) {
            return view(
                'sums',
                [
                    'message' => 'You already have access to SUMS. If you are not able to use equipment, ask in'
                    .' #it-helpdesk in Slack.',
                ]
            );
        }

        return redirect(config('jedi.host').'/self-service/sums');
    }
}
