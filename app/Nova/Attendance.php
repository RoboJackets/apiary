<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use App\Nova\Filters\DateTo;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Filters\DateFrom;
use App\Nova\Filters\Attendable;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Lenses\RecentInactiveUsers;
use App\Nova\Filters\UserActiveAttendance;
use Laravel\Nova\Http\Requests\LensRequest;
use Illuminate\Auth\Access\AuthorizationException;

class Attendance extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Attendance';

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Attendance';
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Attendance Record';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'attendance';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
        ];
    }

    protected function basicFields()
    {
        return [
            Text::make('GTID')
                ->sortable()
                ->rules('required', 'max:255'),

            BelongsTo::make('User', 'attendee'),

            MorphTo::make('Attended', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),

            (new BelongsTo('Recorded By', 'recorded', 'App\Nova\User'))
                ->help('The user that recorded the swipe'),

            DateTime::make('Time', 'created_at')
                ->sortable(),

            Text::make('Source')
                ->hideFromIndex()
                ->sortable(),
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
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new Attendable,
            new UserActiveAttendance,
            new DateFrom,
            new DateTo,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
            new RecentInactiveUsers,
        ];
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

    /**
     * Determine if the current user can view the given resource or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToView(Request $request)
    {
        if ($request instanceof LensRequest) {
            throw new AuthorizationException();
        }
        parent::authorizeToView($request);
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToView(Request $request)
    {
        // This method, and those like it, is a gross way to remove the buttons from the row in the
        // RecentInactiveUsers lens, as they do not work on aggregated rows like that lens uses.
        return ($request instanceof LensRequest) ? false : parent::authorizedToView($request);
    }

    /**
     * Determine if the current user can delete the given resource or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToDelete(Request $request)
    {
        if ($request instanceof LensRequest) {
            throw new AuthorizationException();
        }
        parent::authorizeToDelete($request);
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return ($request instanceof LensRequest) ? false : parent::authorizedToDelete($request);
    }
}
