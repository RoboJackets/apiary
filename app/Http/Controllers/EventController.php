<?php

namespace App\Http\Controllers;

use Bugsnag;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Event;
use App\User;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-events', ['only' => ['index']]);
        $this->middleware('permission:create-events', ['only' => ['store']]);
        $this->middleware('permission:read-events', ['only' => ['show']]);
        $this->middleware('permission:update-events|update-events-own', ['only' => ['update']]);
        $this->middleware('permission:delete-events', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::all();
        return response()->json(['status' => 'success', 'events' => $events]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Default to currently logged-in user
        if (isset($request->organizer) && !$request->has('organizer_id')) {
            $request['organizer_id'] = User::findByIdentifier($request->organizer)->first()->id;
            unset($request['organizer']);
        } elseif (!$request->has('organizer_id')) {
            $request['organizer_id'] = auth()->user()->id;
        }

        $this->validate($request, [
            'name' => 'required|max:255',
            'cost' => 'numeric',
            'allow_anonymous_rsvp' => 'required|boolean',
            'organizer_id' => 'exists:users,id',
            'location' => 'max:255',
            'start_time' => 'date',
            'end_time' => 'date'
        ]);

        try {
            $event = Event::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($event->id)) {
            return response()->json(['status' => 'success', 'event' => $event], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $event = Event::with(['rsvps'])->find($id);

        if ($event) {
            return response()->json(['status' => 'success', 'event' => $event]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'event_not_found'], 404);
        }
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
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'event_not_found'], 404);
        }

        $requestedUser = $event->organizer;
        //Enforce users only viewing themselves (read-users-own)
        if ($requestingUser->cant('update-events') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this Event.'], 403);
        }

        if ($request->has('organizer') && !$request->has('organizer_id')) {
            $request['organizer_id'] = User::findByIdentifier($request->organizer)->first()->id;
            unset($request['organizer']);
        } elseif (!$request->has('organizer_id')) {
            $request['organizer_id'] = auth()->user()->id;
        }

        $this->validate($request, [
            'name' => 'required|max:255',
            'price' => 'numeric|nullable',
            'allow_anonymous_rsvp' => 'required|boolean',
            'organizer_id' => 'required|exists:users,id',
            'location' => 'max:255|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable'
        ]);

        try {
            $event->update($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        $event = Event::find($id);
        if ($event->id) {
            return response()->json(['status' => 'success', 'event' => $event], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
        }
    }

    public function destroy($id)
    {
        $event = Event::find($id);
        $deleted = $event->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'event_deleted']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'event_not_found'], 422);
        }
    }
}
