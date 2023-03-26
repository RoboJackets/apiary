<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Models\Event;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

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
     */
    public function __construct(bool $includeEvents = true)
    {
        $this->includeEvents = $includeEvents;
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>  $query
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>
     */
    public function apply(NovaRequest $request, $query, $value): Builder
    {
        $parts = explode(',', $value);
        $attendableType = $parts[0];
        $attendableID = $parts[1];
        if (! in_array($attendableType, [Event::getMorphClassStatic(), Team::getMorphClassStatic()], true)
            || ! is_numeric($attendableID)) {
            return $query;
        }

        return $query->where('attendable_type', $attendableType)->where('attendable_id', $attendableID);
    }

    /**
     * Get the filter's available options.
     *
     * @return array<string,string>
     */
    public function options(Request $request): array
    {
        // Get all the teams and events (attendables), display them as "Team: <team name>" or "Event: <event name>"
        // Store the value as "App\Models\Team,##" or "App\Models\Event,##", where ## is the ID
        $teams = [];
        if ($request->user()->can('read-teams')) {
            $teams = Team::where('attendable', 1)
                ->when($request->user()->cant('read-teams-hidden'), static function (Builder $query): void {
                    $query->where('visible', 1);
                })->get()->mapWithKeys(
                    static fn (Team $item): array => ['Team: '.$item->name => $item->getMorphClass().','.$item->id]
                )->toArray();
        }

        $events = [];
        if ($this->includeEvents && $request->user()->can('read-events')) {
            $events = Event::all()->mapWithKeys(
                static fn (Event $item): array => ['Event: '.$item->name => $item->getMorphClass().','.$item->id]
            )->toArray();
        }

        return array_merge($teams, $events);
    }
}
