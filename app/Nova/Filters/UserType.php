<?php declare(strict_types = 1);

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserType extends Filter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Type';

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
        return $query->role($value);
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
            'Administrator' => 'admin',
            'Officer' => 'officer',
            'Project Manager' => 'project-manager',
            'Team Lead' => 'team-lead',
            'Trainer' => 'trainer',
            'Member' => 'member',
            'Non-Member' => 'non-member',
        ];
    }
}
