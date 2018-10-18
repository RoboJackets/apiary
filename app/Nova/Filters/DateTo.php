<?php

namespace App\Nova\Filters;

use R64\Filters\DateFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class DateTo extends DateFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereDate('created_at', '<=', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'dateFormat' => 'Y-m-d',
            'placeholder' => 'Pick a date',
            'disabled' => false,
            'twelveHourTime' => false,
            'enableTime' => false,
            'enableSeconds' => false,
        ];
    }
}
