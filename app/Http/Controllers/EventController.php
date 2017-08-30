<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Event;
use App\User;

class EventController extends Controller
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
        if (isset($request->organizer)) {
            $organizer = UserController::getUserByIdentifier($request->organizer);
        } else {
            $organizer = auth()->user();
        }

        $request['organizer'] = $organizer->id;

        $this->validate($request, [
            'name' => 'required|max:255',
            'price' => 'numeric',
            'allow_anonymous_rsvp' => 'required|boolean',
            'location' => 'max:255',
            'start_time' => 'date',
            'end_time' => 'date'
        ]);

        try {
            $event = Event::create($request->all());
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($event->id)) {
            $dbEvent = Event::findOrFail($event->id);
            return response()->json(['status' => 'success', 'event' => $dbEvent], 201);
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
        $event = Event::find($id);
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
        return  response()->json(['status' => 'error', 'message' => 'method_not_implemented'], 501);
    }

    public function destroy($id)
    {
        $event = Event::find();
        $deleted = $event->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'event_deleted']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'event_not_found'], 422);
        }
    }
}
