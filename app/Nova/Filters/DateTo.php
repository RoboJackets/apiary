<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class DateTo extends DateFilter
{
    /**
     * Apply the filter to the given query.
     */
    public function apply(NovaRequest $request, Builder $query, string $value): Builder
    {
        return $query->whereDate('created_at', '<=', Carbon::parse($value));
    }
}
