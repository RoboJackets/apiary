<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Illuminate\Database\Eloquent\Builder;

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
        return $query->whereDate('created_at', '>=', Carbon::parse($value));
    }
}
