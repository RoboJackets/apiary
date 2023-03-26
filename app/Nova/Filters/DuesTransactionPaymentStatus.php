<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

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
     * @param  array<string,bool>  $value
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\DuesTransaction>|\Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesTransaction>
     *
     * @phan-suppress PhanTypeMismatchDeclaredReturn
     */
    public function apply(NovaRequest $request, Builder $query, array $value): Builder
    {
        return $value['pending'] === true ? $query->pending() : $query;
    }

    /**
     * Get the filter's available options.
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
