<?php

namespace App\Http\Controllers;

use Notification;
use Carbon\Carbon;
use App\FasetVisit;
use Illuminate\Http\Request;
use App\Notifications\GeneralInterestNotification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    public function sendNotification()
    {
        $hours = 0;
        FasetVisit::chunk(30, function ($chunk) use (&$hours) {
            $when = Carbon::now()->addHours($hours);
            Notification::send($chunk, (new GeneralInterestNotification())->delay($when));
            $hours++;
        });

        return response()->json(['status' => 'success']);
    }

    public function sendNotificationManual(Request $request)
    {
        if (! $request->has('emails')) {
            return response()->json(['status' => 'error', 'error' => "Missing parameter 'emails'"], 400);
        }

        $hours = 0;
        $found = [];
        $notfound = [];
        $emails = $request->input('emails');
        $chunks = array_chunk($emails, 30);
        foreach ($chunks as $chunk) {
            $when = Carbon::now()->addHours($hours);
            foreach ($chunk as $address) {
                $visit = FasetVisit::where('faset_email', $address)->first();
                if (isset($visit->id)) {
                    Notification::send($visit, (new GeneralInterestNotification())->delay($when));
                    $found[] = $visit->faset_email;
                } else {
                    $notfound[] = $address;
                }
            }
            $hours++;
        }

        return response()->json([
            'status' => 'success',
            'found' => ['count' => count($found), 'emails' => $found],
            'notfound' => ['count' => count($notfound), 'emails' => $notfound],
        ]);
    }
}
