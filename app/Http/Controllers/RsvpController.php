<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\Rsvp;
use App\FasetVisit;
use App\User;

class RsvpController extends Controller
{
    // TODO: Implement RSVP Controller REST API

    public function oneClickCreate(\App\Event $event, Request $request)
    {
        $user = auth()->user();

        if (!$event->allow_anonymous_rsvp) {
            //TODO: Force CAS
        }


        if (isset($request->source)) {
            $source = $request->source;
            $fasetVisit = FasetVisit::where('visit_token', $source)->first();

            if (!is_null($fasetVisit) && !is_null($user)) {
                $fasetVisit['user_id'] = $user->id;
                $fasetVisit->save();
            }
        }

        $rsvp = new Rsvp;

        if (!is_null($user)) {
            $rsvp->user_id = $user->id;
        }

        $rsvp->event_id = $event->id;
        $rsvp->source = $request->source;
        $rsvp->response = "yes";

        $rsvp->saveOrFail();

        return view('giConfirmTemp');
    }
}
