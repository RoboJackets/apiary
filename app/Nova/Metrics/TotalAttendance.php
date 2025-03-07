<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Attendance;
use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class TotalAttendance extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'identification';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        // This is slightly hacky, but it works. Otherwise, responses with a created date of midnight (as created by
        // some forms) were pushed back to the previous day in the metric. This acts like we're in GMT while
        // calculating the attendance.
        $originalTimezone = $request->timezone;
        $request->timezone = 'Etc/GMT';

        $gtid = User::where('id', $request->resourceId)->withTrashed()->first()->gtid;
        // If a subrange is selected, let the library do the work, otherwise just count everything
        $result = $request->range > 0 ? $this->count(
            $request,
            (new Attendance())->newQuery()->where('gtid', $gtid)
        ) : $this->result(
            Attendance::where('gtid', $gtid)->count()
        );

        $request->timezone = $originalTimezone;

        return $result->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    #[\Override]
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
    #[\Override]
    public function uriKey(): string
    {
        return 'total-attendance';
    }
}
