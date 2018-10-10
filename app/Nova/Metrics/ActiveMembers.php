<?php

namespace App\Nova\Metrics;

use App\Team;
use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class ActiveMembers extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        if ($request->resourceId) {
            $count = Team::where('id', $request->resourceId)->get()->first()->members()->active()->count();
            return $this->result($count);
        } else {
            return $this->result(User::active()->count());
        }
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
        return 'active-members';
    }
}
