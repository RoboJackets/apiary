<?php

namespace App\Nova\Metrics;

use App\DuesTransaction;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class MemberSince extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        // Same logic as in DashboardController
        $date = strtotime(DuesTransaction::paid()
            ->where('user_id', $request->resourceId)
            ->with('package')->first()
            ->payment->first()
            ->created_at);
        // The date must be passed in as the prefix, or the non-numeric characters will be stripped and it will be
        // treated as a number. This is ugly but works. See
        // vendor/laravel/nova/resources/js/components/Metrics/Base/ValueMetric.vue line 124.
        return $this->result('')->prefix(date('F j, Y', $date));
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
        return 'member-since';
    }
}
