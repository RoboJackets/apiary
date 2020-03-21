<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\ActiveMembers;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\TotalTeamMembers;
use App\Nova\ResourceTools\CollectAttendance;
use App\Team as AppTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Team::class;

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
        'description',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Textarea::make('Description')
                ->hideFromIndex()
                ->alwaysShow()
                ->rules('required'),

            BelongsTo::make('Project Manager', 'projectManager', User::class)
                ->searchable()
                ->nullable(),

            new Panel('Remote Attendance', $this->remoteAttendanceFields()),

            new Panel('Communications', $this->commFields()),

            new Panel('Controls', $this->controlFields()),

            new Panel('Metadata', $this->metaFields()),

            BelongsToMany::make('Members', 'members', User::class)
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-teams-membership') && $request->user()->can('read-users');
                })
                ->required(true),

            HasMany::make('Attendance')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),

            CollectAttendance::make()
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = AppTeam::find($request->resourceId);
                        // @phan-suppress-next-line PhanTypeExpectedObjectPropAccessButGotNull
                        if (null !== $resource && false === $resource->attendable) {
                            return false;
                        }
                    }

                    return $request->user()->can('create-attendance');
                }),
        ];
    }

    /**
     * Communication-related fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function commFields(): array
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

            Text::make('Slack Private Channel ID')
                ->hideFromIndex()
                ->rules('max:255'),

            Text::make('Google Group')
                ->hideFromIndex()
                ->help('The full email address for the Google Group.')
                ->rules('max:255', 'nullable')
                ->creationRules('unique:teams,google_group')
                ->updateRules('unique:teams,google_group,{{resourceId}}'),
        ];
    }

    /**
     * App internal fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function controlFields(): array
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

    /**
     * Remote attendance fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function remoteAttendanceFields(): array
    {
        return [
            Text::make('Link', 'attendance_secret')
                ->hideFromIndex()
                ->resolveUsing(static function (?string $secret): ?string {
                    return null === $secret ? null : config('app.url').'/attendance/remote/'.$secret;
                })
                ->readOnly(static function (Request $request): bool {
                    // Hidden to non-admins because it's confusing and not useful
                    return ! $request->user()->hasRole('admin');
                })
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                })
                ->creationRules('unique:teams,attendance_secret')
                ->updateRules('unique:teams,attendance_secret,{{resourceId}}'),

            DateTime::make('Expiration', 'attendance_expiration')
                ->hideFromIndex()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                })
                ->readOnly(static function (Request $request): bool {
                    return ! $request->user()->hasRole('admin');
                }),
        ];
    }

    /**
     * Timestamp fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function metaFields(): array
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
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [
            (new TotalTeamMembers())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-teams-membership');
                }),
            (new ActiveMembers())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-teams-membership');
                }),
            (new AttendancePerWeek())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),
            (new ActiveAttendanceBreakdown())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new Actions\ResetRemoteAttendance())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                })
                ->canRun(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                }),
        ];
    }

    /**
     * Build an "index" query for the team resource to hide hidden teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $request->user()->cant('read-teams-hidden') ? $query->where('visible', 1) : $query;
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        return $request->user()->cant('read-teams-hidden') ? $query->where('visible', 1) : $query;
    }
}
