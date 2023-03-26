<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class DateFrom extends DateFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $value
     */
    public function apply(NovaRequest $request, $query, $value): Builder
    {
        return $query->whereDate('created_at', '>=', Carbon::parse($value));
    }
}
