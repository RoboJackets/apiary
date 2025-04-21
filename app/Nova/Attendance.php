<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Attendance as AppModelsAttendance;
use App\Models\User as AppModelsUser;
use App\Nova\Actions\CreateUserFromAttendance;
use App\Nova\Filters\Attendable;
use App\Nova\Filters\DateFrom;
use App\Nova\Filters\DateTo;
use App\Nova\Filters\UserActiveAttendance;
use App\Nova\Lenses\RecentInactiveUsers;
use App\Nova\Metrics\AttendanceSourceBreakdown;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;

/**
 * A Nova resource for attendance.
 *
 * @extends \App\Nova\Resource<\App\Models\Attendance>
 */
class Attendance extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Attendance::class;

    /**
     * Get the displayable label of the resource.
     */
    #[\Override]
    public static function label(): string
    {
        return 'Attendance';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[\Override]
    public static function singularLabel(): string
    {
        return 'Attendance Record';
    }

    /**
     * Get the URI key for the resource.
     */
    #[\Override]
    public static function uriKey(): string
    {
        return 'attendance';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

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
        'recorded',
        'attendee',
        'attendable',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(Request $request): array
    {
        return [
            Text::make('GTID')
                ->hideFromIndex()
                ->rules('required', 'max:255')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->resolveUsing(fn (?string $gtid): ?string => $this->attendee !== null ? null : $gtid)
                ->copyable(),

            BelongsTo::make('Access Card')
                ->hideFromIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            BelongsTo::make('User', 'attendee')
                ->searchable(),

            MorphTo::make('Attended', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),

            BelongsTo::make('Recorded By', 'recorded', User::class)
                ->help('The user that recorded the swipe')
                ->searchable(),

            DateTime::make('Time', 'created_at')
                ->sortable(),

            Text::make('Source')
                ->hideFromIndex()
                ->sortable(),

            BelongsTo::make('Remote Attendance Link', 'remoteAttendanceLink')
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-remote-attendance-links')),

            self::metadataPanel(),
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
            (new AttendanceSourceBreakdown())->width('1/2'),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    #[\Override]
    public function filters(Request $request): array
    {
        return [
            new Attendable(),
            new UserActiveAttendance(),
            new DateFrom(),
            new DateTo(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    #[\Override]
    public function lenses(Request $request): array
    {
        return [
            (new RecentInactiveUsers())->canSee(
                static fn (Request $request): bool => $request->user()->can('read-attendance')
            ),
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
        $resourceId = $request->resourceId ?? $request->resources;

        if ($resourceId === null || (is_array($resourceId) && count($resourceId) > 1)) {
            return [];
        }

        if (is_array($resourceId) && count($resourceId) === 1) {
            $resourceId = $resourceId[0];
        }

        return [
            (new CreateUserFromAttendance())->canRun(
                static fn (Request $request): bool => $request->user()->can('create-users')
            )->canSee(
                static fn (Request $request): bool => AppModelsUser::where(
                    'gtid',
                    AppModelsAttendance::whereId($resourceId)
                        ->sole()->gtid
                )->doesntExist()
            ),
        ];
    }

    /**
     * Determine if the current user can view the given resource or throw an exception.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    #[\Override]
    public function authorizeToView(Request $request): void
    {
        if ($request instanceof LensRequest) {
            throw new AuthorizationException();
        }
        parent::authorizeToView($request);
    }

    /**
     * Determine if the current user can view the given resource.
     */
    #[\Override]
    public function authorizedToView(Request $request): bool
    {
        // This method, and those like it, is a gross way to remove the buttons from the row in the
        // RecentInactiveUsers lens, as they do not work on aggregated rows like that lens uses.
        return $request instanceof LensRequest ? false : parent::authorizedToView($request);
    }

    /**
     * Determine if the current user can delete the given resource or throw an exception.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    #[\Override]
    public function authorizeToDelete(Request $request): void
    {
        if ($request instanceof LensRequest) {
            throw new AuthorizationException();
        }
        parent::authorizeToDelete($request);
    }

    /**
     * Determine if the current user can delete the given resource.
     */
    #[\Override]
    public function authorizedToDelete(Request $request): bool
    {
        return $request instanceof LensRequest ? false : parent::authorizedToDelete($request);
    }
}
