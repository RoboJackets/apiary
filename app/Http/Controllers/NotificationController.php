<?php

namespace App\Http\Controllers;

use Mail;
use Notification;
use Carbon\Carbon;
use App\RecruitingVisit;
use Illuminate\Http\Request;
use App\Mail\DatabaseMailable;
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
        RecruitingVisit::chunk(30, function ($chunk) use (&$hours) {
            $when = Carbon::now()->addHours($hours);
            Notification::send($chunk, (new GeneralInterestNotification())->delay($when));
            $hours++;
        });

        return response()->json(['status' => 'success']);
    }

    public function sendNotificationManual(Request $request)
    {
        $this->validate($request, [
            'emails' => 'required',
            'template_type' => 'required|in:recruiting,database',
            'template_id' => 'numeric',
        ]);

        $template_type = $request->input('template_type');
        $template_id = $request->input('template_id');

        $hours = 0;
        $found = [];
        $notfound = [];
        $emails = $request->input('emails');
        $chunks = array_chunk($emails, 30);
        foreach ($chunks as $chunk) {
            $when = Carbon::now()->addHours($hours);
            if ($template_type == 'recruiting') {
                foreach ($chunk as $address) {
                    $visit = RecruitingVisit::where('recruiting_email', $address)->first();
                    if (isset($visit->id)) {
                        Notification::send($visit, (new GeneralInterestNotification())->delay($when));
                        $found[] = $visit->recruiting_email;
                    } else {
                        $notfound[] = $address;
                    }
                }
                $hours++;
            } elseif ($template_type == 'database') {
                foreach ($chunk as $address) {
                    Mail::to($address)->send(new DatabaseMailable($template_id, null));
                    $found[] = $address;
                }
                $hours++;
            } else {
                return response()->json(['status' => 'error', 'error' => 'Invalid template type']);
            }
        }

        return response()->json([
            'status' => 'success',
            'found' => ['count' => count($found), 'emails' => $found],
            'notfound' => ['count' => count($notfound), 'emails' => $notfound],
        ]);
    }
}
