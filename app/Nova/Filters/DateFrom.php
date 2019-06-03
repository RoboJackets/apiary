<?php declare(strict_types = 1);

namespace App\Nova\Filters;

use R64\Filters\DateFilter;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class DateFrom extends DateFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, Builder $query, string $value): Builder
    {
        return $query->whereDate('created_at', '>=', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request): array
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
