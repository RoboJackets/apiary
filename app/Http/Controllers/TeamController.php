<?php

namespace App\Http\Controllers;

use App\Team;
use App\User;
use Illuminate\Http\Request;
use App\Traits\AuthorizeInclude;
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $include = $request->input('include');
        $teamsQ = Team::with($this->authorizeInclude(Team::class, $include));
        $teams = (\Auth::user()->can('read-teams-hidden')) ? $teamsQ->get() : $teamsQ->visible()->get();

        return response()->json(['status' => 'success', 'teams' => TeamResource::collection($teams)]);
    }

    public function indexWeb()
    {
        $teams = Team::visible()->orderBy('name', 'asc')->get();

        // Send only what's necessary to the front end
        $user_id = auth()->user()->id;
        // Lazy load the teams relationship and send the `id`s over
        $user_teams = auth()->user()->teams;
        $user = [
            'id' => $user_id,
            'teams' => $user_teams,
        ];

        return view('teams.index')->with(['teams' => $teams, 'user' => json_encode($user)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:teams',
            'description' => 'string|max:4096|nullable',
            'attendable' => 'boolean',
            'visible' => 'boolean',
            'self_serviceable' => 'boolean',
            'mailing_list_name' => 'string|nullable',
            'slack_channel_id' => 'string|nullable',
            'slack_channel_name' => 'string|nullable',
        ]);

        try {
            $team = Team::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($team->id)) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int  $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $include = $request->input('include');
        $team = Team::with($this->authorizeInclude(Team::class, $include))
            ->where('id', $id)->orWhere('slug', $id)->first();

        if ($team && $team->visible == false && \Auth::user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        } elseif ($team) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }
    }

    /**
     * Returns a list of all members of the given team.
     *
     * @param $id integer Team ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMembers($id)
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        $members = $team->members;

        if ($team && $team->visible == false && \Auth::user()->cant('read-teams-hidden')) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        return response()->json(['status' => 'success', 'members' => UserResource::collection($members)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if (! $team || ($team->visible == false && \Auth::user()->cant('update-teams-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        $this->validate($request, [
            'name' => 'string',
            'description' => 'string|max:4096|nullable',
            'attendable' => 'boolean',
            'hidden' => 'boolean',
            'self_serviceable' => 'boolean',
            'mailing_list_name' => 'string|nullable',
            'slack_channel_id' => 'string|nullable',
            'slack_channel_name' => 'string|nullable',
        ]);

        try {
            $team->update($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($team->id)) {
            return response()->json(['status' => 'success', 'team' => new TeamResource($team)], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
        }
    }

    /**
     * Updates membership of the given team.
     *
     * @param Request $request
     * @param $id integer
     * @return \Illuminate\Http\Response
     */
    public function updateMembers(Request $request, $id)
    {
        $requestingUser = $request->user();

        $this->validate($request, [
            'user_id' => 'required|numeric|exists:users,id',
            'action' => 'required|in:join,leave',
        ]);

        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        if (! $team || ($team->visible == false && \Auth::user()->cant('update-teams-hidden'))) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        //Enforce users only updating themselves (update-teams-membership-own)
        if ($requestingUser->cant('update-teams') && $requestingUser->id != $request->input('user_id')) {
            return response()->json(['status' => 'error',
                'message' => 'no_priv_for_target_user', ], 403);
        }

        //Enforce updating membership via self-service only for eligible teams
        if ($requestingUser->cant('update-teams') && $team->self_serviceable == false) {
            return response()->json(['status' => 'error',
                'message' => 'self_service_disabled', ], 403);
        }

        try {
            $user = User::find($request->input('user_id'));
            if ($request->input('action') == 'join') {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $team = Team::where('id', $id)->orWhere('slug', $id)->first();
        $deleted = $team->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'team_deleted']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'team_not_found', ], 422);
        }
    }
}
