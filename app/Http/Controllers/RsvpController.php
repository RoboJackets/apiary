<?php

namespace App\Http\Controllers;

use Auth;
use App\Rsvp;
use App\User;
use App\Event;
use App\RecruitingVisit;
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
     * Stores a user-submitted RSVP resource.
     *
     * @param Event $event
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function storeUser(Event $event, Request $request)
    {
        // Get the user to store, if present
        // If not present and required, redirect to CAS
        $user = auth()->user();
        if (! $event->allow_anonymous_rsvp && ! Auth::check()) {
            cas()->authenticate();
        }

        // Link to recruiting visit if the user is logged in
        if ($request->filled('token')) {
            $source = 'email';
            $token = $request->input('token');
            $recruitingVisit = RecruitingVisit::where('visit_token', $token)->first();

            if (! is_null($recruitingVisit) && ! is_null($user)) {
                $recruitingVisit['user_id'] = $user->id;
                $recruitingVisit->save();
            }
        }

        $rsvp = new Rsvp;

        if (! is_null($user)) {
            $rsvp->user_id = $user->id;
        }

        $rsvp->ip_address = $request->ip();
        $rsvp->user_agent = $request->userAgent();
        $rsvp->event_id = $event->id;
        $rsvp->source = (isset($source)) ? $source : $request->input('source');
        $rsvp->response = 'yes';

        $rsvp->saveOrFail();

        return view('rsvp.confirmation')->with(['event' => $event]);
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
}
