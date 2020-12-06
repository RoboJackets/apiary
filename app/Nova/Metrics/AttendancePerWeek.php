<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Attendance;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class AttendancePerWeek extends Trend
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): TrendResult
    {
        // This is slightly hacky, but it works. Otherwise, responses with a created date of midnight (as created by
        // some forms) were pushed back to the previous day in the metric. This acts like we're in GMT while
        // calculating the attendance.
        $request->timezone = 'Etc/GMT';

        $query = Attendance::class;

        // If we're on a team page, not the main dashboard, filter to that team
        if (isset($request->resourceId)) {
            $query = (new Attendance())
                ->newQuery()
                ->where('attendable_id', $request->resourceId)
                ->where('attendable_type', Team::getMorphClass());
        }

        // Aggregate based on counting distinct values in the gtid column
        $column = DB::raw('distinct attendance.gtid');
        $result = $this->aggregate($request, $query, Trend::BY_WEEKS, 'count', $column, 'created_at')
            ->showLatestValue();

        return $result;
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    public function ranges(): array
    {
        return [
            8 => '2 Months',
            12 => '3 Months',
            26 => '6 Months',
            52 => '1 Year',
        ];
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'attendance-per-week';
    }
}
