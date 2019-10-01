<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Team;
use App\User;
use App\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Metrics\ValueResult;

class PrimaryTeam extends TextMetric
{
    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\ValueResult
     */
    public function calculate(Request $request): ValueResult
    {
        $gtid = User::where('id', $request->resourceId)->first()->gtid;
        $teams = Attendance::where('gtid', $gtid)
            ->where('attendable_type', \App\Team::class);

        if (is_numeric($request->range) && $request->range >= -2) {
            // For the purposes of this, the spring semester runs January - April, summer runs May - July, and fall
            // runs August - December
            $date = now()->startOfDay();
            $intrange = intval($request->range);
            if (-1 === $intrange) {
                // Find start of semester date
                if ($date->month <= 4) {
                    $date = $date->month(1)->day(1);
                } elseif ($date->month <= 7) {
                    $date = $date->month(5)->day(1);
                } else {
                    $date = $date->month(8)->day(1);
                }
            } elseif (-2 === $intrange) {
                // Find the most recent August 1
                $date = $date->month(8)->day(1);
                if ($date->greaterThan(now())) {
                    $date = $date->subYear();
                }
            } else {
                $date = $date->subDays($intrange);
            }
            $teams = $teams->whereBetween('created_at', [$date, now()]);
        }

        $teams = $teams->groupBy('attendable_id')
            ->select('attendable_id', DB::raw('count(*) as count'))
            ->get()
            ->toArray();

        $max = 0;
        $maxTeamIDs = [];
        foreach ($teams as $item) {
            if ($item['count'] === $max) {
                $maxTeamIDs[] = $item['attendable_id'];
            } elseif ($item['count'] > $max) {
                $max = $item['count'];
                $maxTeamIDs = [$item['attendable_id']];
            }
        }

        if (0 === count($maxTeamIDs)) {
            return $this->result('No attendance');
        }

        $names = Team::whereIn('id', $maxTeamIDs)->get()->pluck('name')->toArray();

        return $this->result(implode(', ', $names));
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    public function ranges(): array
    {
        return [
            -1 => 'This Semester',
            -2 => 'This Academic Year',
            -3 => 'All Time',
            30 => 'Last 30 Days',
            60 => 'Last 60 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'primary-team';
    }
}
