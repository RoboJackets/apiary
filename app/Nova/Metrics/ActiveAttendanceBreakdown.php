<?php

namespace App\Nova\Metrics;

use App\User;
use App\Attendance;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class ActiveAttendanceBreakdown extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return $this->showAllTime ? 'Active Attendees' : 'Attendees Last 4 Weeks';
    }

    /**
     * Whether to show based on all attendance records or only those from the last two weeks.
     *
     * @var bool
     */
    protected $showAllTime = false;

    /**
     * Create a new ActiveAttendanceBreakdown metric.
     *
     * @param  bool  $showAllTime
     */
    public function __construct($showAllTime = false)
    {
        $this->showAllTime = $showAllTime;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $query = Attendance::selectRaw('count(distinct gtid) as aggregate')
            // If a user is found, this will give "Active" in the active column, otherwise the column will be null
            ->selectSub(User::whereRaw('attendance.gtid = users.gtid')->active()->selectRaw("'Active'"), 'active');

        if ($request->resourceId) {
            $query = $query->where('attendable_id', $request->resourceId)
                ->where('attendable_type', get_class($request->model()));
        }

        if (! $this->showAllTime) {
            $query = $query->whereBetween('created_at', [now()->subDays(28)->startOfDay(), now()]);
        }

        $result = $query->groupBy('active')
            ->orderByRaw('aggregate desc, active desc')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->active;
                if (! $item->active) {
                    $key = 'Inactive';
                }

                return [$key => $item->aggregate];
            })->toArray();

        return $this->result($result);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'active-attendance-breakdown';
    }
}
