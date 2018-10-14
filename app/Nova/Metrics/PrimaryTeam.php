<?php

namespace App\Nova\Metrics;

use App\Team;
use App\User;
use App\Attendance;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Illuminate\Support\Facades\DB;

class PrimaryTeam extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $gtid = User::where('id', $request->resourceId)->first()->gtid;
        $teams = Attendance::where('gtid', $gtid)
            ->where('attendable_type', 'App\Team')
            ->groupBy('attendable_id')
            ->select('attendable_id', DB::raw('count(*) as count'))
            ->get()->toArray();

        $max = 0;
        $maxTeamIDs = [];
        foreach ($teams as $item) {
            if ($item['count'] == $max) {
                $maxTeamIDs[] = $item['attendable_id'];
            } elseif ($item['count'] > $max) {
                $max = $item['count'];
                $maxTeamIDs = [$item['attendable_id']];
            }
        }

        if (count($maxTeamIDs) == 0) {
            return $this->result('No teams or attendance');
        } else {
            $names = Team::whereIn('id', $maxTeamIDs)->get()->pluck('name')->toArray();

            return $this->result(implode(', ', $names));
        }
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [];
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
        return 'primary-team';
    }
}
