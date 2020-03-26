<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator

namespace App\Nova\Metrics;

use App\Attendance;
use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class TotalAttendance extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        // This is slightly hacky, but it works. Otherwise, responses with a created date of midnight (as created by
        // some forms) were pushed back to the previous day in the metric. This acts like we're in GMT while
        // calculating the attendance.
        $originalTimezone = $request->timezone;
        $request->timezone = 'Etc/GMT';

        $gtid = User::where('id', $request->resourceId)->first()->gtid;
        // If a subrange is selected, let the library do the work, otherwise just count everything
        if ($request->range > 0) {
            $result = $this->count($request, (new Attendance())->newQuery()->where('gtid', $gtid));
        } else {
            $result = $this->result(Attendance::where('gtid', $gtid)->count());
        }

        $request->timezone = $originalTimezone;

        return $result->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    public function ranges(): array
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
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'total-attendance';
    }
}
