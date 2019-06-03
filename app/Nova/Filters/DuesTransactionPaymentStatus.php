<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;
use Illuminate\Database\Eloquent\Builder;

class DuesTransactionPaymentStatus extends BooleanFilter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Payment Status';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param array<string>  $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, Builder $query, array $value): Builder
    {
        return $value['pending'] ? $query->pending() : $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<string,string>
     */
    public function options(Request $request): array
    {
        return [
            'Only Pending' => 'pending',
        ];
    }
}
