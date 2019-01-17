<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use App\Nova\Metrics\ActiveMembers;
use App\Nova\Metrics\TotalTeamMembers;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Metrics\AttendancePerWeek;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Metrics\ActiveAttendanceBreakdown;

class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Team';

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
        'description',
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
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Textarea::make('Description')
                ->hideFromIndex()
                ->alwaysShow()
                ->rules('required'),

            new Panel('Communications', $this->commFields()),

            new Panel('Controls', $this->controlFields()),

            new Panel('Metadata', $this->metaFields()),

            BelongsToMany::make('User', 'members')->canSee(function ($request) {
                return $request->user()->can('read-teams-membership') && $request->user()->can('read-users');
            }),

            HasMany::make('Attendance')->canSee(function ($request) {
                return $request->user()->can('read-attendance');
            }),
        ];
    }

    protected function commFields()
    {
        return [
            Text::make('Mailing List Name')
                ->hideFromIndex()
                ->sortable()
                ->rules('max:255'),

            Text::make('Slack Channel Name')
                ->hideFromIndex()
                ->rules('max:255'),

            Text::make('Slack Channel ID')
                ->hideFromIndex()
                ->rules('max:255'),
        ];
    }

    protected function controlFields()
    {
        return [
            Boolean::make('Visible')
                ->sortable(),

            Boolean::make('Attendable')
                ->sortable(),

            Boolean::make('Self-Serviceable', 'self_serviceable')
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
        return [
            (new TotalTeamMembers())
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-teams-membership');
                }),
            (new ActiveMembers())
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-teams-membership');
                }),
            (new AttendancePerWeek())
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-attendance');
                }),
            (new ActiveAttendanceBreakdown())
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->can('read-attendance');
                }),
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

    /**
     * Build an "index" query for the team resource to hide hidden teams.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if (! $request->user()->can('read-teams-hidden')) {
            return $query->where('visible', 1);
        } else {
            return $query;
        }
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        if (! $request->user()->can('read-teams-hidden')) {
            return $query->where('visible', 1);
        } else {
            return $query;
        }
    }
}
