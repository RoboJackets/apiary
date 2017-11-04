<?php

namespace App\Http\Controllers;

use App\Attendance;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-attendance', ['only' => ['index']]);
        $this->middleware('permission:create-attendance', ['only' => ['store']]);
        $this->middleware('permission:read-attendance|read-attendance-own', ['only' => ['show']]);
        $this->middleware('permission:update-attendance', ['only' => ['update']]);
        $this->middleware('permission:delete-attendance', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendance = Attendance::all();
        return response()->json(['status' => 'success', 'attendance' => $attendance]);
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
            'attendable_type' => 'required|string',
            'attendable_id' => 'required|numeric',
            'gtid' => 'numeric|max:9|exists:users',
            'source' => 'required|string',
            'recorded_by' => 'required|numeric|exists:users,id'
        ]);

        try {
            $att = Attendance::create($request->all());
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($att->id)) {
            $dbAtt = Attendance::findOrFail($att->id);
            return response()->json(['status' => 'success', 'attendance' => $dbAtt], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id integer Resource ID
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = auth()->user();
        $att = Attendance::find($id);
        if ($att && ($att->gtid == $user->gtid || $user->can('read-attendance'))) {
            return response()->json(['status' => 'success', 'attendance' => $att]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Attendance not found.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id integer Resource ID
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'attendable_type' => 'string',
            'attendable_id' => 'numeric',
            'gtid' => 'numeric|exists:users',
            'source' => 'string',
            'recorded_by' => 'numeric|exists:users'
        ]);

        $att = Attendance::find($id);
        if ($att) {
            $att->update($request->all());
        } else {
            return response()->json(['status' => 'error', 'message' => 'Attendance not found.'], 404);
        }

        $att = Attendance::find($att->id);
        if ($att) {
            return response()->json(['status' => 'success', 'attendance' => $att]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id integer Resource ID
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $att = Attendance::find($id);
        $deleted = $att->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'Attendance deleted.']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'Attendance does not exist or was previously deleted.'], 422);
        }
    }
}
