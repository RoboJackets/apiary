<?php

namespace App\Http\Controllers;

use App\Team;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-teams', ['only' => ['index']]);
        $this->middleware('permission:create-teams', ['only' => ['store']]);
        $this->middleware('permission:read-teams', ['only' => ['show']]);
        $this->middleware('permission:update-teams', ['only' => ['update']]);
        $this->middleware('permission:delete-teams', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teams = Team::all();
        return response()->json(['status' => 'success', 'teams' => $teams]);
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
            'description' => 'string|max:255|nullable',
            'founding_semester' => 'numeric|required'
        ]);

        try {
            $team = Team::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($team->id)) {
            return response()->json(['status' => 'success', 'team' => $team], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $team = Team::find($id);

        if ($team) {
            return response()->json(['status' => 'success', 'team' => $team]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }
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
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['status' => 'error', 'message' => 'team_not_found'], 404);
        }

        $this->validate($request, [
            'name' => 'string|unique:teams|nullable',
            'description' => 'string|max:255|nullable',
            'founding_semester' => 'numeric'
        ]);

        try {
            $team->update($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($team->id)) {
            return response()->json(['status' => 'success', 'team' => $team], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $team = Team::find($id);
        $deleted = $team->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'team_deleted']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'team_not_found'], 422);
        }
    }
}
