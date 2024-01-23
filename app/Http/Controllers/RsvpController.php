<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Event as EventResource;
use App\Http\Resources\Rsvp as RsvpResource;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use App\Util\AuthorizeInclude;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $rsvps = Rsvp::with(AuthorizeInclude::authorize(Rsvp::class, $include))->get();

        return response()->json(['status' => 'success', 'rsvps' => RsvpResource::collection($rsvps)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $requestingUser = $request->user();
        $requestedUser = User::findByIdentifier($request->input('user_id'))->first();
        //Enforce users only creating RSVPs for themselves (create-rsvps-own)
        if ($requestingUser->cant('create-rsvps') && $requestingUser->id !== $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You may not create an RSVP for another user.',
            ], 403);
        }

        return response()->json(['status' => 'error', 'message' => 'method_not_implemented'], 501);
    }

    /**
     * Stores a user-submitted RSVP resource.
     */
    public function storeUser(Event $event, Request $request)
    {
        // Get the user to store, if present
        // If not present and required, redirect to CAS
        $user = $request->user();
        if ($event->allow_anonymous_rsvp === false && $request->user() === null) {
            cas()->authenticate();
        }

        $now = new DateTime();
        $end = isset($event->end_time) ? new DateTime($event->end_time->toDateTimeString()) : null;
        if ($end !== null && $end <= $now) {
            return view('rsvp.ended')->with(['event' => $event]);
        }

        if (
            $user === null ||
            Rsvp::where('user_id', '=', $user->id)->where('event_id', '=', $event->id)->doesntExist()
        ) {
            $rsvp = new Rsvp();

            if ($user !== null) {
                $rsvp->user_id = $user->id;
            }

            $rsvp->ip_address = $request->ip();
            $rsvp->user_agent = null;
            if ($request->userAgent() !== null) {
                $rsvp->user_agent = Str::limit($request->userAgent(), 1023, '');
            }
            $rsvp->event_id = $event->id;
            $rsvp->source = $request->input('source');
            $rsvp->response = 'yes';

            $rsvp->saveOrFail();
        }

        return view('rsvp.confirmation')->with(['event' => new EventResource($event)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $requestingUser = $request->user();
        $rsvp = Rsvp::find($id);
        if ($rsvp === null) {
            return response()->json(['status' => 'error', 'message' => 'rsvp_not_found'], 404);
        }

        //Enforce users only updating RSVPs for themselves (update-rsvps-own)
        $requestedUser = $rsvp->user;
        if ($requestingUser->cant('update-rsvps') && $requestingUser->id !== $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You may not update an RSVP for another user.',
            ], 403);
        }

        return response()->json(['status' => 'error', 'message' => 'method_not_implemented'], 501);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $requestingUser = $request->user();
        $rsvp = Rsvp::find($id);
        if ($rsvp === null) {
            return response()->json(['status' => 'error', 'message' => 'rsvp_not_found'], 404);
        }

        //Enforce users only deleting RSVPs for themselves (update-rsvps-own)
        $requestedUser = $rsvp->user;
        if ($requestingUser->cant('delete-rsvps') && $requestingUser->id !== $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You may not delete an RSVP for another user.',
            ], 403);
        }

        if ($rsvp->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'event_deleted']);
        }

        return response()->json(['status' => 'error',
            'message' => 'event_not_found',
        ], 422);
    }
}
