<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\RsvpSourceBreakdown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;

/**
 * A Nova resource for events.
 *
 * @extends \App\Nova\Resource<\App\Models\Event>
 */
class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Event::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Meetings';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'organizer',
    ];

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 2;

    /**
     * The number of results to display when searching the resource using Scout.
     *
     * @var int
     */
    public static $scoutSearchResults = 2;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Event ID', 'id')
                ->onlyOnDetail(),
            Text::make('Event Name', 'name')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:events,name')
                ->updateRules('unique:events,name,{{resourceId}}'),

            BelongsTo::make('Organizer', 'organizer', User::class)
                ->searchable()
                ->rules('required')
                ->withoutTrashed(),

            DateTime::make('Start Time')
                ->rules('nullable', 'date', 'before:end_time'),

            DateTime::make('End Time')
                ->rules('nullable', 'date', 'after:start_time'),

            Text::make('Location')
                ->hideFromIndex()
                ->rules('max:255'),

            Boolean::make('Allow Anonymous RSVPs', 'allow_anonymous_rsvp')
                ->help(
                    'Selecting this option allows members to RSVP to this event without logging in to '.
                    config('app.name').'. Deselecting it requires everyone to log in to record their RSVP.'
                )
                ->hideFromIndex(),

            Text::make('RSVP URL', fn (): string => route('events.rsvp', ['event' => $this->id]))
                ->onlyOnDetail()
                ->copyable(),

            MorphMany::make('Remote Attendance Links', 'remoteAttendanceLinks')
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-remote-attendance-links')),

            HasMany::make('RSVPs')
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-rsvps')),

            MorphMany::make('Attendance')
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance')),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [
            (new RsvpSourceBreakdown())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-rsvps')),
            (new ActiveAttendanceBreakdown(true))
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance')),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        if ($this->end_time < Carbon::now()) {
            return [];
        }

        return [
            (new Actions\CreateRemoteAttendanceLink())
                ->canSee(static fn (Request $request): bool => $request->user()->can('create-remote-attendance-links'))
                ->canRun(static fn (Request $request): bool => $request->user()->can('create-remote-attendance-links'))
                ->confirmText('Are you sure you want to create a remote attendance link?')
                ->confirmButtonText('Create Link')
                ->cancelButtonText('Cancel'),
        ];
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        if ($this->start_time === null && $this->end_time !== null) {
            return $this->end_time->format('F jS, Y');
        }

        if ($this->start_time !== null) {
            return $this->start_time->format('F jS, Y');
        }

        return null;
    }
}
