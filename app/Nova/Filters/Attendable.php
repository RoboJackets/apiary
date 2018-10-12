<?php

namespace App\Nova\Filters;

use App\Team;
use App\Event;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class Attendable extends Filter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Attended';

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
        $parts = explode(',', $value);
        $attendableType = $parts[0];
        $attendableID = $parts[1];
        return $query->where('attendable_type', $attendableType)->where('attendable_id', $attendableID);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        // Get all the teams and events (attendables), display them as "Team: <team name>" or "Event: <event name>"
        // Store the value as "App\Team,##" or "App\Event,##", where ## is the ID
        $teams = Team::where('attendable', 1)->get()->mapWithKeys(function($item) {
            return ['Team: '.$item['name'] => 'App\Team,'.$item['id']];
        })->toArray();
        $events = Event::all()->mapWithKeys(function($item) {
            return ['Event: '.$item['name'] => 'App\Event,'.$item['id']];
        })->toArray();
        return array_merge($teams, $events);
    }
}
