<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Team as AppModelsTeam;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\ActiveMembers;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\TotalTeamMembers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for teams.
 *
 * @extends \App\Nova\Resource<\App\Models\Team>
 */
class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Team::class;

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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'projectManager',
    ];

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
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 5;

    /**
     * The number of results to display when searching the resource using Scout.
     *
     * @var int
     */
    public static $scoutSearchResults = 5;

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:teams,name')
                ->updateRules('unique:teams,name,{{resourceId}}'),

            Textarea::make('Description')
                ->hideFromIndex()
                ->alwaysShow()
                ->rules('required'),

            BelongsTo::make('Project Manager', 'projectManager', User::class)
                ->help(view('nova.help.team.projectmanager')->render())
                ->searchable()
                ->nullable()
                ->withoutTrashed(),

            new Panel('Communications', $this->commFields()),

            new Panel('Controls', $this->controlFields()),

            BelongsToMany::make('Members', 'members', User::class)
                ->canSee(
                    static fn (Request $request): bool => $request->user()->can(
                        'read-teams-membership'
                    ) && $request->user()->can(
                        'read-users'
                    )
                ),

            MorphMany::make('Remote Attendance Links', 'remoteAttendanceLinks')
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = AppModelsTeam::find($request->resourceId);
                        if ($resource !== null && is_a($resource, AppModelsTeam::class) && ! $resource->attendable) {
                            return false;
                        }
                    }

                    return $request->user()->can('read-remote-attendance-links');
                }),

            MorphMany::make('Attendance')
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance')),

            self::metadataPanel(),
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
                ->rules('max:255')
                ->copyable(),

            Text::make('Slack Channel ID')
                ->hideFromIndex()
                ->rules('max:255')
                ->copyable(),

            Text::make('Slack Private Channel ID')
                ->hideFromIndex()
                ->rules('max:255')
                ->copyable(),

            Email::make('Google Group', 'google_group')
                ->hideFromIndex()
                ->help('The full email address for the Google Group.')
                ->rules('max:255', 'nullable', 'email:rfc,strict,dns,spoof')
                ->creationRules('unique:teams,google_group')
                ->updateRules('unique:teams,google_group,{{resourceId}}')
                ->copyable(),
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
            Boolean::make('Visible to Members', 'visible')
                ->sortable(),

            Boolean::make('Visible on Kiosk', 'visible_on_kiosk')
                ->sortable(),

            Boolean::make('Attendable')
                ->sortable(),

            Boolean::make('Self-Serviceable', 'self_serviceable')
                ->sortable(),

            Boolean::make('Self-Service Override Eligible', 'self_service_override_eligible')
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    #[\Override]
    public function cards(Request $request): array
    {
        return [
            (new TotalTeamMembers())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-teams-membership')),
            (new ActiveMembers())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-teams-membership')),
            (new AttendancePerWeek())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = AppModelsTeam::find($request->resourceId);
                        if ($resource !== null && is_a($resource, AppModelsTeam::class) && ! $resource->attendable) {
                            return false;
                        }
                    }

                    return $request->user()->can('read-attendance');
                }),
            (new ActiveAttendanceBreakdown())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = AppModelsTeam::find($request->resourceId);
                        if ($resource !== null && is_a($resource, AppModelsTeam::class) && ! $resource->attendable) {
                            return false;
                        }
                    }

                    return $request->user()->can('read-attendance');
                }),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    #[\Override]
    public function actions(Request $request): array
    {
        return [
            (new Actions\CreateRemoteAttendanceLink())
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = AppModelsTeam::find($request->resourceId);
                        if ($resource !== null && is_a($resource, AppModelsTeam::class) && ! $resource->attendable) {
                            return false;
                        }
                    }

                    return $request->user()->can('create-remote-attendance-links');
                })
                ->canRun(static fn (Request $request): bool => $request->user()->can('create-remote-attendance-links'))
                ->confirmText('Are you sure you want to create a remote attendance link?')
                ->confirmButtonText('Create Link')
                ->cancelButtonText('Cancel'),
        ];
    }

    /**
     * Build an "index" query for the team resource to hide hidden teams.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Team>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Team>
     */
    #[\Override]
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $request->user()->cant('read-teams-hidden') ? $query->where('visible', 1) : $query;
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Team>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Team>
     */
    #[\Override]
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        return $request->user()->cant('read-teams-hidden') ? $query->where('visible', 1) : $query;
    }

    /**
     * Get the search result subtitle for the resource.
     */
    #[\Override]
    public function subtitle(): ?string
    {
        if ($this->projectManager !== null) {
            return 'Project Manager: '.$this->projectManager->full_name;
        }

        return null;
    }
}
