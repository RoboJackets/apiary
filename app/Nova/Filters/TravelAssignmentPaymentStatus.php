<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class TravelAssignmentPaymentStatus extends BooleanFilter
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
     * @psalm-pure
     */
    #[\Override]
    public function apply(NovaRequest $request, Builder $query, mixed $value): Builder
    {
        return $value['pending'] === true ? $query->unpaid()->whereNull('charged_off_at') : $query;
    }

    /**
     * Get the filter's available options.
     *
     * @psalm-pure
     *
     * @return array<string, string>
     */
    #[\Override]
    public function options(NovaRequest $request): array
    {
        return [
            'Only Pending' => 'pending',
        ];
    }
}
