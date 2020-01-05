<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class DuesTransactionSwagStatus extends BooleanFilter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Swag Status';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string>  $value
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apply(Request $request, $query, $value)
    {
        return $value['pending'] ? $query->pendingSwag() : $query;
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
