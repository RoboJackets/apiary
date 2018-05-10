<?php

namespace App\Http\Controllers;

use Auth;
use App\Rsvp;
use App\User;
use App\Event;
use App\FasetVisit;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-rsvps', ['only' => ['index']]);
        $this->middleware('permission:create-rsvps|create-rsvps-own', ['only' => ['store']]);
        $this->middleware('permission:read-rsvps|read-rsvps-own', ['only' => ['show']]);
        $this->middleware('permission:update-rsvps|update-rsvps-own', ['only' => ['update']]);
        $this->middleware('permission:delete-rsvps|delete-rsvps-own', ['only' => ['destroy']]);
    }

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
        $requestingUser = $request->user();
        $requestedUser = User::findByIdentifier($request->input('user_id'))->first();
        //Enforce users only creating RSVPs for themselves (create-rsvps-own)
        if ($requestingUser->cant('create-rsvps') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You may not create an RSVP for another user.', ], 403);
        }

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
        $requestingUser = $request->user();
        $rsvp = Rsvp::find($id);
        if (! $rsvp) {
            return response()->json(['status' => 'error', 'message' => 'rsvp_not_found'], 404);
        }

        //Enforce users only updating RSVPs for themselves (update-rsvps-own)
        $requestedUser = $rsvp->user;
        if ($requestingUser->cant('update-rsvps') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You may not update an RSVP for another user.', ], 403);
        }

        return response()->json(['status' => 'error', 'message' => 'method_not_implemented'], 501);
    }

    public function destroy(Request $request, $id)
    {
        $requestingUser = $request->user();
        $rsvp = Rsvp::find($id);
        if (! $rsvp) {
            return response()->json(['status' => 'error', 'message' => 'rsvp_not_found'], 404);
        }

        //Enforce users only deleting RSVPs for themselves (update-rsvps-own)
        $requestedUser = $rsvp->user;
        if ($requestingUser->cant('delete-rsvps') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You may not delete an RSVP for another user.', ], 403);
        }

        $deleted = $rsvp->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'event_deleted']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'event_not_found', ], 422);
        }
    }

    public function oneClickCreate(Event $event, Request $request)
    {
        $user = auth()->user();
        if (! $event->allow_anonymous_rsvp && ! Auth::check()) {
            cas()->authenticate();
        }

        if (isset($request->source)) {
            $source = $request->source;
            $fasetVisit = FasetVisit::where('visit_token', $source)->first();

            if (! is_null($fasetVisit) && ! is_null($user)) {
                $fasetVisit['user_id'] = $user->id;
                $fasetVisit->save();
            }
        }

        $rsvp = new Rsvp;

        if (! is_null($user)) {
            $rsvp->user_id = $user->id;
        }

        $rsvp->event_id = $event->id;
        $rsvp->source = $request->source;
        $rsvp->response = 'yes';

        $rsvp->saveOrFail();

        return view('giConfirmTemp');
    }
}
