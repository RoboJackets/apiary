<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\Rsvp;
use App\FasetVisit;
use App\User;

class RsvpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @api {get} /events/ List all events
     * @apiGroup Users
     */
    public function index()
    {
        $rsvps = Rsvp::all();
        return response()->json(['status' => 'success', 'rsvps' => $rsvps]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return  response()->json(['status' => 'error', 'message' => 'method_not_implemented'], 501);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return  response()->json(['status' => 'error', 'message' => 'method_not_implemented'], 501);
    }

    public function destroy($id)
    {
        $rsvp = Rsvp::find();
        $deleted = $rsvp->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'event_deleted']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'event_not_found'], 422);
        }
    }

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
