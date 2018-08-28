<?php

namespace App\Http\Controllers;

use App\Mail\DatabaseMailable;
use Mail;
use Carbon\Carbon;
use App\RecruitingVisit;
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
        RecruitingVisit::chunk(30, function ($chunk) use (&$hours) {
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

        $template = $request->input('template_id');

        $hours = 0;
        $queued = [];
        $emails = $request->input('emails');
        $chunks = array_chunk($emails, 30);
        foreach ($chunks as $chunk) {
            $when = Carbon::now()->addHours($hours);
            foreach ($chunk as $address) {
                Mail::to($address)->send(new DatabaseMailable($template, null));
                $queued[] = $address;
            }
            $hours++;
        }

        return response()->json([
            'status' => 'success',
            'queued' => ['count' => count($queued), 'emails' => $queued],
        ]);
    }
}
