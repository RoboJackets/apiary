<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMembersTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Requests\StoreTeamRequest;
use App\Team;
use App\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Traits\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use App\Http\Resources\Team as TeamResource;
use App\Http\Resources\User as UserResource;

class TeamController extends Controller
{
    use AuthorizeInclude;

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
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $teamsQ = Team::with($this->authorizeInclude(Team::class, $include));
        $teams = $request->user()->can('read-teams-hidden') ? $teamsQ->get() : $teamsQ->visible()->get();

        return response()->json(['status' => 'success', 'teams' => TeamResource::collection($teams)]);
    }

    public function indexWeb(Request $request): View
    {
        $teams = Team::visible()->orderBy('name', 'asc')->get();

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
     *
     * @param \App\Http\Requests\StoreTeamRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {

        try {
            $team = Team::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($team->id)) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param string  $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $team = Team::with($this->authorizeInclude(Team::class, $include))
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (null !== $team && false === $team->visible && $request->user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        if (null !== $team) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)]);
        }

        return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
    }

    /**
     * Returns a list of all members of the given team.
     *
     * @param string $id integer Team ID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMembers(Request $request, string $id): JsonResponse
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        $members = $team->members;

        if ($team && false === $team->visible && $request->user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        return response()->json(['status' => 'success', 'members' => UserResource::collection($members)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateTeamRequest  $request
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTeamRequest $request, string $id): JsonResponse
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if (! $team || (false === $team->visible && $request->user()->cant('update-teams-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }


        try {
            $team->update($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($team->id)) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    /**
     * Updates membership of the given team.
     *
     * @param \App\Http\Requests\UpdateMembersTeamRequest $request
     * @param string $id integer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMembers(UpdateMembersTeamRequest $request, string $id): JsonResponse
    {
        $requestingUser = $request->user();


        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if (! $team || (false === $team->visible && $request->user()->cant('update-teams-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        //Enforce users only updating themselves (update-teams-membership-own)
        if ($requestingUser->cant('update-teams') && $requestingUser->id !== $request->input('user_id')) {
            return response()->json(['status' => 'error',
                'message' => 'no_priv_for_target_user',
            ], 403);
        }

        //Enforce updating membership via self-service only for eligible teams
        if ($requestingUser->cant('update-teams') && false === $team->self_serviceable) {
            return response()->json(['status' => 'error',
                'message' => 'self_service_disabled',
            ], 403);
        }

        try {
            $user = User::find($request->input('user_id'));
            if ('join' === $request->input('action')) {
                $team->members()->syncWithoutDetaching($user);
            } else {
                $team->members()->detach($user);
            }
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        $team = new TeamResource(Team::where('id', $id)->orWhere('slug', $id)->first());

        return response()->json(['status' => 'success', 'team' => $team, 'member' => $user->name], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if ($team->delete()) {
            return response()->json(['status' => 'success', 'message' => 'team_deleted']);
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'team_not_found',
            ],
            422
        );
    }
}
