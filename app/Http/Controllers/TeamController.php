<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateMembersTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\Team as TeamResource;
use App\Http\Resources\User as UserResource;
use App\Models\Team;
use App\Models\User;
use App\Util\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-teams', ['only' => ['index', 'indexWeb', 'show', 'showMembers']]);
        $this->middleware('permission:create-teams', ['only' => ['store']]);
        $this->middleware('permission:update-teams', ['only' => ['update']]);
        $this->middleware('permission:update-teams|update-teams-membership-own', ['only' => ['updateMembers']]);
        $this->middleware('permission:delete-teams', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $teamsQ = Team::with(AuthorizeInclude::authorize(Team::class, $include));
        $teams = $request->user()->can('read-teams-hidden') ? $teamsQ->get() : $teamsQ->visible()->get();

        return response()->json(['status' => 'success', 'teams' => TeamResource::collection($teams)]);
    }

    /**
     * Displays the teams page for users.
     */
    public function indexWeb(Request $request)
    {
        $teams = Team::visible()->orderBy('visible_on_kiosk', 'desc')->orderBy('name')->get();

        // Send only what's necessary to the front end
        $user_id = $request->user()->id;
        // Lazy load the teams relationship and send the `id`s over
        $user_teams = $request->user()->teams;
        $user = [
            'id' => $user_id,
            'teams' => $user_teams,
        ];

        return view('teams.index')->with(['teams' => $teams, 'user' => json_encode($user)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = Team::create($request->validated());

        if ($team->id !== null) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $include = $request->input('include');
        $team = Team::with(AuthorizeInclude::authorize(Team::class, $include))
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if ($team !== null && $team->visible === false && $request->user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        if ($team !== null) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)]);
        }

        return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
    }

    /**
     * Returns a list of all members of the given team.
     */
    public function showMembers(Request $request, string $id): JsonResponse
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if ($team === null) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        if ($team->visible === false && $request->user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        return response()->json(['status' => 'success', 'members' => UserResource::collection($team->members)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, string $id): JsonResponse
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if ($team === null || ($team->visible === false && $request->user()->cant('update-teams-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        $team->update($request->validated());

        return response()->json(['status' => 'success', 'team' => new TeamResource($team)], 201);
    }

    /**
     * Updates membership of the given team.
     */
    public function updateMembers(UpdateMembersTeamRequest $request, string $id): JsonResponse
    {
        $requestingUser = $request->user();

        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if ($team === null || ($team->visible === false && $request->user()->cant('update-teams-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        //Enforce users only updating themselves (update-teams-membership-own)
        if ($requestingUser->cant('update-teams') && $requestingUser->id !== $request->input('user_id')) {
            return response()->json(['status' => 'error',
                'message' => 'no_priv_for_target_user',
            ], 403);
        }

        //Enforce updating membership via self-service only for eligible teams
        if ($requestingUser->cant('update-teams') && $team->self_serviceable === false) {
            return response()->json(['status' => 'error',
                'message' => 'self_service_disabled',
            ], 403);
        }

        $user = User::find($request->input('user_id'));
        if ($user === null || ! is_a($user, User::class)) {
            return response()->json(['status' => 'user_not_found'], 400);
        }
        if ($request->input('action') === 'join') {
            $team->members()->syncWithoutDetaching($user);
        } else {
            $team->members()->detach($user);
        }

        $team = new TeamResource(Team::where('id', $id)->orWhere('slug', $id)->first());

        return response()->json(['status' => 'success', 'team' => $team, 'member' => $user->name], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if ($team === null) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'team_not_found',
                ],
                422
            );
        }

        if ($team->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'team_deleted']);
        }

        return response()->json(['status' => 'error'], 500);
    }
}
