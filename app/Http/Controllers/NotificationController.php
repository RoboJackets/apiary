<?php

namespace App\Http\Controllers;

use App\FasetVisit;
use App\Notifications\GeneralInterestNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification()
    {
        $all_faset_vists = FasetVisit::all();
        Notification::send($all_faset_vists, new GeneralInterestNotification());
        return response()->json(['status' => 'success']);
    }
}
