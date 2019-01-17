<?php

namespace App\Nova\Filters;

use DB;
use App\Team;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class DuesTransactionTeam extends Filter
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereExists(function ($query) use ($value) {
            $query->select(DB::raw(1))
                  ->from('team_user')
                  ->where('dues_transactions.user_id', '=', 'team_user.user_id')
                  ->where('team_user.team_id', '=', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $teams = [];
        if ($request->user()->can('read-teams')) {
            $teams = Team::where('attendable', 1)
                ->when($request->user()->cant('read-teams-hidden'), function ($query) {
                    $query->where('visible', 1);
                })->get()
                ->mapWithKeys(function ($item) {
                    return [$item['name'] => $item['id']];
                })->toArray();
        }

        return $teams;
    }
}
