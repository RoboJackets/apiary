<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Http\Controllers;

use App\Team;
use Carbon\Carbon;
use App\Jobs\PushToJedi;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SUMSController extends Controller
{
    /**
     * Returns view for SUMS status.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->is_access_active) {
            return view(
                'sums',
                [
                    'message' => 'You have not paid dues, so you do not have access to SUMS right now.',
                ]
            );
        }

        $lastAttendance = $user->attendance()->where('attendable_type', Team::class)
            ->orderBy('created_at', 'desc')->first();

        if (null !== $lastAttendance
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
                    'message' => 'You already have access to SUMS. If you are not able to use equipment, please ask in'
                    .' #it-helpdesk in Slack.',
                ]
            );
        }

        PushToJedi::dispatch($user, self::class, 1, 'sums-self-service-ux');

        usleep(100000);

        $counter = 0;
        while ($counter < 20) {
            $user->refresh(); // reloads attributes from database

            if ($user->exists_in_sums) {
                break;
            }

            $counter++;
            usleep($counter * 100000);
        }

        if ($user->exists_in_sums) {
            return view(
                'sums',
                [
                    'message' => 'You have been successfully added to the RoboJackets group in SUMS. You should now be '
                    .'able to use the kiosk in the Common Machining Area. If you have any issues, please ask in '
                    .'#it-helpdesk on Slack.',
                ]
            );
        }

        return view(
            'sums',
            [
                'message' => 'There was a problem processing your SUMS access. Please ask in '
                .'#it-helpdesk on Slack for further assistance.',
            ]
        );
    }
}
