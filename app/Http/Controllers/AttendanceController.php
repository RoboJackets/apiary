<?php

namespace App\Http\Controllers;

use Log;
use Bugsnag;
use App\Attendance;
use Illuminate\Http\Request;
use App\Traits\AuthorizeInclude;
use Illuminate\Database\QueryException;
use App\Http\Resources\Attendance as AttendanceResource;

class AttendanceController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware('permission:read-attendance', ['only' => ['index', 'search']]);
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
        $include = $request->input('include');
        $att = Attendance::with($this->authorizeInclude(Attendance::class, $include))->get();

        return response()->json(['status' => 'success', 'attendance' => AttendanceResource::collection(($att))]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $include = $request->input('include');
        unset($request['include']);

        $this->validate($request, [
            'attendable_type' => 'required|string',
            'attendable_id' => 'required|numeric',
            'gtid' => 'required|numeric',
            'source' => 'required|string',
            'created_at' => 'date',
        ]);

        $request['recorded_by'] = $request->user()->id;

        // Variables for comparison below
        $date = $request->input('created_at', date('Y-m-d'));
        $gtid = $request->input('gtid');

        try {
            $attExistingQ = Attendance::where($request->all())->whereDate('created_at', $date);
            $attExistingCount = $attExistingQ->count();
            if ($attExistingCount > 0) {
                Log::debug(get_class().": Found a swipe on $date for $gtid - ignoring.");
                $att = $attExistingQ->first();
                $code = 200;
            } else {
                Log::debug(get_class().": No swipe yet on $date for $gtid - saving.");
                $att = Attendance::create($request->all());
                $code = 201;
            }
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        // Yes this is kinda gross but it's the best that I could come up with
        // This is mainly to allow for requesting the attendee relationship for showing the name on swipes
        $dbAtt = Attendance::with($this->authorizeInclude(Attendance::class, $include))->find($att->id);

        return response()->json(['status' => 'success', 'attendance' => new AttendanceResource($dbAtt)], $code);
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
        $include = $request->input('include');
        $user = auth()->user();
        $att = Attendance::with($this->authorizeInclude(Attendance::class, $include))->find($id);
        if ($att && ($att->gtid == $user->gtid || $user->can('read-attendance'))) {
            return response()->json(['status' => 'success', 'attendance' => new AttendanceResource($att)]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Attendance not found.'], 404);
        }
    }

    /**
     * Searches attendance records for specified data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $include = $request->input('include');
        $this->validate($request, [
            'attendable_type' => 'required',
            'attendable_id' => 'required|numeric',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
        ]);

        $att = Attendance::where('attendable_type', '=', $request->input('attendable_type'))
            ->where('attendable_id', '=', $request->input('attendable_id'))
            ->start($request->input('start_date'))->end($request->input('end_date'))
            ->with($this->authorizeInclude(Attendance::class, $include))->get();

        if ($att) {
            return response()->json(['status' => 'success', 'attendance' => AttendanceResource::collection($att)]);
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
            'recorded_by' => 'numeric|exists:users',
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

            return response()->json(['status' => 'success', 'attendance' => new AttendanceResource($att)]);
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
                'message' => 'Attendance does not exist or was previously deleted.', ], 422);
        }
    }
}
