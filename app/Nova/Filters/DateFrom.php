<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;

class DateFrom extends DateFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $value
     */
    public function apply(Request $request, $query, $value): Builder
    {
        return $query->whereDate('created_at', '>=', Carbon::parse($value));
    }
}
