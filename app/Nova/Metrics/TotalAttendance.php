<?php

namespace App\Nova\Metrics;

use App\User;
use App\Attendance;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class TotalAttendance extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        // This is slightly hacky, but it works. Otherwise, responses with a created date of midnight (as created by
        // some forms) were pushed back to the previous day in the metric. This acts like we're in GMT while
        // calculating the attendance.
        $originalTimezone = $request->timezone;
        $request->timezone = 'Etc/GMT';

        $gtid = User::where('id', $request->resourceId)->first()->gtid;
        // If a subrange is selected, let the library do the work, otherwise just count everything
        if ($request->range > 0) {
            $result = $this->count($request, (new Attendance())
                ->newQuery()
                ->where('gtid', $gtid)
            );
        } else {
            $result = $this->result(Attendance::where('gtid', $gtid)->count());
        }

        $request->timezone = $originalTimezone;
        return $result;
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            -1 => 'All',
            7 => '7 Days',
            14 => '14 Days',
            30 => '30 Days',
            60 => '60 Days',
            365 => '365 Days',
        ];
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
        return 'total-attendance';
    }
}
