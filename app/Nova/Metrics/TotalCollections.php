<?php

namespace App\Nova\Metrics;

use App\Payment;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class TotalCollections extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $query = Payment::where('payable_type', 'App\DuesTransaction')
            ->whereIn('payable_id', function ($q) use ($request) {
                $q->select('id')
                    ->from('dues_transactions')
                    ->where('dues_package_id', $request->resourceId)
                    ->whereNull('deleted_at');
            });
        if ($request->range > 0) {
            $query = $query->whereBetween('created_at', [now()->subDays($request->range)->startOfDay(), now()]);
        }

        return $this->result($query->sum('amount'))->dollars();
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
        return 'total-collections';
    }
}
