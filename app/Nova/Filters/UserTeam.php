<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserTeam extends Filter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Team';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

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
        return $query->whereHas('teams', static function (Builder $query) use ($value): void {
            $query->where('teams.id', '=', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array<string,string>
     */
    #[\Override]
    public function options(Request $request): array
    {
        $teams = [];
        if ($request->user()->can('read-teams')) {
            $teams = Team::where('attendable', 1)
                ->when($request->user()->cant('read-teams-hidden'), static function (Builder $query): void {
                    $query->where('visible', 1);
                })->get()
                ->mapWithKeys(static fn (Team $item): array => [$item->name => $item->id])->toArray();
        }

        return $teams;
    }
}
