<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Metrics\RsvpSourceBreakdown;
use App\Nova\Metrics\ActiveAttendanceBreakdown;

class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Event';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel('Basic Information', $this->basicFields()),

            new Panel('Metadata', $this->metaFields()),

            HasMany::make('RSVPs'),

            HasMany::make('Attendance'),
        ];
    }

    protected function basicFields()
    {
        return [
            Text::make('Event Name', 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            (new BelongsTo('Organizer', 'organizer', 'App\Nova\User'))
                ->searchable()
                ->rules('required')
                // default to self
                ->help('The organizer of the event'),

            DateTime::make('Start Time')
                ->hideFromIndex(),

            DateTime::make('End Time')
                ->hideFromIndex(),

            Text::make('Location')
                ->hideFromIndex()
                ->rules('max:255'),

            Currency::make('Cost')
                ->hideFromIndex(),

            Boolean::make('Anonymous RSVP', 'allow_anonymous_rsvp')
                ->hideFromIndex(),

            Text::make('RSVP URL', function () {
                return route('events.rsvp', ['event' => $this->id]);
            }),
        ];
    }

    protected function metaFields()
    {
        return [
            DateTime::make('Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new RsvpSourceBreakdown())->onlyOnDetail(),
            (new ActiveAttendanceBreakdown(true))->onlyOnDetail(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
