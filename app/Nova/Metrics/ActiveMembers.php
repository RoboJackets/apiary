<?php declare(strict_types = 1);

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
            $count = Team::where('id', $request->resourceId)
                ->get()
                ->first()
                ->members()
                ->active()
                ->count();

            return $this->result($count);
        }

        return $this->result(User::active()->count());
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'active-members';
    }
}
