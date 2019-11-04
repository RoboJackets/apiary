<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\Event as EventResource;
use App\Traits\AuthorizeInclude;
use Bugsnag;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use AuthorizeInclude;

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
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $events = Event::with($this->authorizeInclude(Event::class, $include))->get();

        return response()->json(['status' => 'success', 'events' => EventResource::collection($events)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreEventRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        try {
            $event = Event::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($event->id)) {
            return response()->json(['status' => 'success', 'event' => $event], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $event = Event::with($this->authorizeInclude(Event::class, $include))->find($id);

        if (null !== $event) {
            return response()->json(['status' => 'success', 'event' => new EventResource($event)]);
        }

        return response()->json(['status' => 'error', 'message' => 'event_not_found'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateEventRequest $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, int $id): JsonResponse
    {
        $requestingUser = $request->user();
        $event = Event::find($id);
        if (! $event) {
            return response()->json(['status' => 'error', 'message' => 'event_not_found'], 404);
        }

        $requestedUser = $event->organizer;
        //Enforce users only viewing themselves (read-users-own)
        if ($requestingUser->cant('update-events') && $requestingUser->id !== $requestedUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this Event.',
            ], 403);
        }

        try {
            $event->update($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        $event = Event::find($id);
        if ($event->id) {
            return response()->json(['status' => 'success', 'event' => new EventResource($event)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    public function destroy(int $id): JsonResponse
    {
        $event = Event::find($id);
        if ($event->delete()) {
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
