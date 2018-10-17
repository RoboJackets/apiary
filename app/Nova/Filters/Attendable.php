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
     * Whether to include events in the attendable options.
     *
     * @var bool
     */
    protected $includeEvents = true;

    /**
     * Create new Attendable filter.
     *
     * @param  bool  $includeEvents
     */
    public function __construct($includeEvents = true)
    {
        $this->includeEvents = $includeEvents;
    }

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
        if (! in_array($attendableType, ['App\Event', 'App\Team']) || ! is_numeric($attendableID)) {
            return $query;
        }

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
        $teams = Team::where('attendable', 1)->get()->mapWithKeys(function ($item) {
            return ['Team: '.$item['name'] => 'App\Team,'.$item['id']];
        })->toArray();
        $events = $this->includeEvents ? Event::all()->mapWithKeys(function ($item) {
            return ['Event: '.$item['name'] => 'App\Event,'.$item['id']];
        })->toArray() : [];

        return array_merge($teams, $events);
    }
}
