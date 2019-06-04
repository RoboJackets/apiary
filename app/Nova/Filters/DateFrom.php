<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

namespace App\Nova\Filters;

use R64\Filters\DateFilter;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class DateFrom extends DateFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param string  $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value): Builder
    {
        return $query->whereDate('created_at', '>=', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<string,string|false>
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
