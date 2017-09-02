<?php

namespace App\Http\Controllers;

use App\FasetVisit;
use App\Notifications\GeneralInterestNotification;
use Carbon\Carbon;
use Notification;
use Mail;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
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
        if (!$request->has('emails')) {
            return response()->json(['status' => 'error', 'error' => "Missing parameter 'emails'"], 400);
        }
        
        $hours = 0;
        $emails = $request->input('emails');
        $chunks = array_chunk($emails, 30);
        foreach ($chunks as $chunk) {
            $when = Carbon::now()->addHours($hours);
            foreach ($chunk as $address) {
                Mail::to($address)->later($when, new GeneralInterestNotification());
            }
            $hours++;
        }
        return response()->json(['status' => 'success', 'count' => count($emails)]);
    }
}
