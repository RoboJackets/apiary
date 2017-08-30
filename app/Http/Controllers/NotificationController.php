<?php

namespace App\Http\Controllers;

use App\FasetVisit;
use App\Notifications\GeneralInterestNotification;
use Carbon\Carbon;
use Notification;
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
}
