<?php

namespace App\Nova\Metrics;

use App\Attendance;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class AttendancePerWeek extends Trend
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

        // If we're on a team page, not the main dashboard, filter to that team
        if ($request->resourceId) {
            $result = $this->countByWeeks($request, (new Attendance())
                ->newQuery()
                ->where('attendable_id', $request->resourceId)
                ->where('attendable_type', 'App\Team')
            )->showLatestValue();
        } else {
            $result = $this->countByWeeks($request, Attendance::class)->showLatestValue();
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
            4 => '1 Month',
            8 => '2 Months',
            12 => '3 Months',
            26 => '6 Months',
            52 => '1 Year',
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
        return 'attendance-per-week';
    }
}
