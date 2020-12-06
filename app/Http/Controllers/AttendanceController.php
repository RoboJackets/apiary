<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchAttendanceRequest;
use App\Http\Requests\StatisticsAttendanceRequest;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\Attendance as AttendanceResource;
use App\Jobs\PushToJedi;
use App\Models\Attendance;
use App\Models\Team;
use App\Models\User;
use App\Traits\AuthorizeInclude;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware('permission:read-attendance', ['only' => ['index', 'search', 'statistics']]);
        $this->middleware('permission:create-attendance', ['only' => ['store']]);
        $this->middleware('permission:read-attendance|read-attendance-own', ['only' => ['show']]);
        $this->middleware('permission:update-attendance', ['only' => ['update']]);
        $this->middleware('permission:delete-attendance', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $att = Attendance::with($this->authorizeInclude(Attendance::class, $include))->get();

        return response()->json(['status' => 'success', 'attendance' => AttendanceResource::collection($att)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $include = $request->input('include');
        unset($request['include']);

        $request['recorded_by'] = $request->user()->id;

        // Variables for comparison below
        $date = $request->input('created_at', date('Y-m-d'));
        $gtid = $request->input('gtid');
        $user = User::where('gtid', '=', $gtid)->first();

        try {
            $attExistingQ = Attendance::where($request->only(['attendable_type', 'attendable_id', 'gtid']))
                ->whereDate('created_at', $date);
            $attExistingCount = $attExistingQ->count();
            if ($attExistingCount > 0) {
                Log::debug(self::class.': Found a swipe on '.$date.' for '.$gtid.' - ignoring.');
                $att = $attExistingQ->first();
                $code = 200;

                if (null !== $user) {
                    PushToJedi::dispatch($user, self::class, -1, 'duplicate-attendance');
                }
            } else {
                Log::debug(self::class.': No swipe yet on '.$date.' for '.$gtid.' - saving.');
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
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $include = $request->input('include');
        $user = $request->user();
        $att = Attendance::with($this->authorizeInclude(Attendance::class, $include))->find($id);
        if (null !== $att && ($att->gtid === $user->gtid || $user->can('read-attendance'))) {
            return response()->json(['status' => 'success', 'attendance' => new AttendanceResource($att)]);
        }

        return response()->json(['status' => 'error', 'message' => 'Attendance not found.'], 404);
    }

    /**
     * Searches attendance records for specified data.
     */
    public function search(SearchAttendanceRequest $request): JsonResponse
    {
        $include = $request->input('include');

        $att = Attendance::where('attendable_type', '=', $request->input('attendable_type'))
            ->where('attendable_id', '=', $request->input('attendable_id'))
            ->start($request->input('start_date'))->end($request->input('end_date'))
            ->with($this->authorizeInclude(Attendance::class, $include))->get();

        return response()->json(['status' => 'success', 'attendance' => AttendanceResource::collection($att)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, int $id): JsonResponse
    {
        $att = Attendance::find($id);
        if (null !== $att) {
            try {
                $att->update($request->all());
            } catch (QueryException $e) {
                Bugsnag::notifyException($e);
                $errorMessage = $e->errorInfo[2];

                return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
            }

            return response()->json(['status' => 'success', 'attendance' => new AttendanceResource($att)]);
        }

        return response()->json(['status' => 'error', 'message' => 'Attendance not found.'], 404);
    }

    // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id Resource ID
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $att = Attendance::find($id);
        if (true === $att->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Attendance deleted.']);
        }

        return response()->json(['status' => 'error',
            'message' => 'Attendance does not exist or was previously deleted.',
        ], 422);
    }

    // phpcs:enable

    /**
     * Give a summary of attendance from the given time period.
     */
    public function statistics(StatisticsAttendanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $numberOfWeeks = intval($request->input('range', '52'));
        $startDay = now()->subWeeks($numberOfWeeks)->startOfDay();
        $endDay = now();

        // Get average attendance by day of week from the range given. Selects the weekday number and the weekday name
        // so it can be sorted more easily; the number is trimmed out in the map method
        $attendanceByDay = Attendance::whereBetween('created_at', [$startDay, $endDay])
            ->where('attendable_type', Team::getMorphClassStatic())
            ->selectRaw('date_format(created_at, \'%w%W\') as day, count(gtid) as aggregate')
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get()
            ->mapWithKeys(static function (object $item) use ($numberOfWeeks): array {
                return [substr($item->day, 1) => $item->aggregate / $numberOfWeeks];
            });

        $attendanceByDayAndTeam = Attendance::whereBetween('created_at', [$startDay, $endDay])
            ->where('attendable_type', Team::getMorphClassStatic())
            ->selectRaw('attendable_id, date_format(created_at, \'%w%W\') as day, count(gtid) as aggregate')
            ->groupBy('day', 'attendable_id')
            ->orderBy('day', 'asc')
            ->get()
            ->groupBy('attendable_id')
            ->mapWithKeys(static function (Collection $item, int $attendable_id) use ($numberOfWeeks, $user): array {
                // If the user can't read teams only give them the attendable_id
                $teamName = $user->can('read-teams') ? Team::find($attendable_id)->name : $attendable_id;

                return [
                    $teamName => collect([
                        'Sunday' => 0,
                        'Monday' => 0,
                        'Tuesday' => 0,
                        'Wednesday' => 0,
                        'Thursday' => 0,
                        'Friday' => 0,
                        'Saturday' => 0,
                    ])->merge($item->mapWithKeys(static function (object $day) use ($numberOfWeeks): array {
                        return [
                            substr($day->day, 1) => $day->aggregate / $numberOfWeeks,
                        ];
                    })),
                ];
            });

        $averageWeeklyAttendance = (Attendance::whereBetween('created_at', [$startDay, $endDay])
            ->where('attendable_type', Team::getMorphClassStatic())
            ->selectRaw('date_format(created_at, \'%Y %U\') as week, count(distinct gtid) as aggregate')
            ->groupBy('week')
            ->get()
            ->sum('aggregate')) / $numberOfWeeks;

        // phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

        // Get the attendance by (ISO) week for the teams, for all time so historical graphs can be generated
        $attendanceByTeam = Attendance::selectRaw(
            'date_format(attendance.created_at, \'%x %v\') as week, count(distinct gtid) as aggregate, attendable_id, '
            .'teams.name, teams.visible'
        )->where('attendable_type', Team::getMorphClassStatic())
            ->when($user->cant('read-teams-hidden'), static function (Builder $query): void {
                $query->where('visible', 1);
            })->leftJoin('teams', 'attendance.attendable_id', '=', 'teams.id')
            ->groupBy('week', 'attendable_id')
            ->orderBy('visible', 'desc')
            ->orderBy('name', 'asc')
            ->orderBy('week', 'asc')
            ->get();

        // phpcs:enable
        // phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

        // If the user can't read teams only give them the attendable_id
        if ($user->can('read-teams')) {
            $attendanceByTeam = $attendanceByTeam->groupBy('name');
        } else {
            $attendanceByTeam = $attendanceByTeam->groupBy('attendable_id');
        }

        // phpcs:enable

        // Return only the team ID/name, the day, and the count of records on that day
        $attendanceByTeam = $attendanceByTeam->map(static function (Collection $item): Collection {
            return $item->pluck('aggregate', 'week');
        });

        $statistics = [
            'averageDailyMembers' => $attendanceByDay,
            'averageDailyMembersByTeam' => $attendanceByDayAndTeam,
            'averageWeeklyMembers' => $averageWeeklyAttendance,
            'byTeam' => $attendanceByTeam,
        ];

        return response()->json(['status' => 'success', 'statistics' => $statistics]);
    }
}
