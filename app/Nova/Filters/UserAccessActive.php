<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserAccessActive extends Filter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Access Active';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    #[\Override]
    public function apply(NovaRequest $request, $query, $value): Builder
    {
        return $value === 'yes' ? $query->accessActive() : $query->accessInactive();
    }

    /**
     * Get the filter's available options.
     *
     * @return array<string,string>
     */
    #[\Override]
    public function options(Request $request): array
    {
        return [
            'Yes' => 'yes',
            'No' => 'no',
        ];
    }
}
