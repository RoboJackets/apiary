<?php

namespace App\Http\Controllers;

use Log;
use Bugsnag;
use App\User;
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
    public function index(Request $request)
    {
        $this->validate($request, [
            'attendable_type' => 'string',
            'attendable_id' => 'numeric'
        ]);

        $attendable_type = $request->attendable_type;
        $attendable_id = $request->attendable_id;

        if (empty($attendable_type) || empty($attendable_id)) {
            $attendance = Attendance::with('attendee')->get();
        } else {
            $attendance = Attendance::where('attendable_type', $attendable_type)
                ->where('attendable_id', $attendable_id)
                ->with('attendee')
                ->get();
        }

        
        return response()->json(['status' => 'success', 'attendance' => $attendance]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'attendable_type' => 'required|string',
            'attendable_id' => 'required|numeric',
            'gtid' => 'required|numeric',
            'source' => 'required|string',
            'created_at' => 'date'
        ]);

        $wantsName = ($request->has('includeName'));
        $request['recorded_by'] = $request->user()->id;
        unset($request['includeName']);

        // Variables for comparison below
        $today = date('Y-m-d');
        $gtid = $request->input('gtid');

        try {
            $attTodayQ = Attendance::where($request->all())->whereDate('created_at', $today);
            $attTodayCount = $attTodayQ->count();
            if ($attTodayCount > 0) {
                Log::debug(get_class() . ": Found a swipe today ($today) for $gtid - ignoring.");
                $att = $attTodayQ->first();
                $code = 200;
            } else {
                Log::debug(get_class() . ": No swipe yet today ($today) for $gtid - saving.");
                $att = Attendance::create($request->all());
                $code = 201;
            }
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        //Return user's name, if found and if requested
        //This array merge is only okay until Fractal is implemented
        if ($wantsName) {
            $user = User::where('gtid', '=', $request->input('gtid'))->first();
            $name = ($user) ? ["name" => $user->name] : ["name" => "Non-Member"];
            $att = array_merge($att->toArray(), $name);
        }

        return response()->json(['status' => 'success', 'attendance' => $att], $code);
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
            try {
                $att->update($request->all());
            } catch (QueryException $e) {
                Bugsnag::notifyException($e);
                $errorMessage = $e->errorInfo[2];
                return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
            }
            return response()->json(['status' => 'success', 'attendance' => $att]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Attendance not found.'], 404);
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
