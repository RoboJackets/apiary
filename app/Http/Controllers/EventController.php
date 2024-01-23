<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\Event as EventResource;
use App\Models\Event;
use App\Util\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $events = Event::with(AuthorizeInclude::authorize(Event::class, $include))->get();

        return response()->json(['status' => 'success', 'events' => EventResource::collection($events)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create($request->validated());

        return response()->json(['status' => 'success', 'event' => $event], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        $include = $request->input('include');
        $event = Event::with(AuthorizeInclude::authorize(Event::class, $include))->find($event->id);

        if ($event !== null) {
            return response()->json(['status' => 'success', 'event' => new EventResource($event)]);
        }

        return response()->json(['status' => 'error', 'message' => 'event_not_found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $requestingUser = $request->user();

        $requestedUser = $event->organizer;
        if ($requestingUser->cant('update-events') && $requestingUser->id !== $requestedUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this Event.',
            ], 403);
        }

        $event->update($request->validated());

        $event = Event::find($event->id);
        if ($event !== null) {
            return response()->json(['status' => 'success', 'event' => new EventResource($event)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    public function destroy(Event $event): JsonResponse
    {
        if ($event->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'event_deleted']);
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'event_not_found',
            ],
            422
        );
    }
}
