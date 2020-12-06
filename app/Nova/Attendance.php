<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Actions\ExportAttendance;
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
use Laravel\Nova\Panel;

/**
 * A Nova resource for attendance.
 *
 * @property ?\App\Models\User $attendee The user associated with the attendance record, if available
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
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'Attendance';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Attendance Record';
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
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
    public static $with = ['recorded', 'attendee'];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('GTID')
                ->hideFromIndex()
                ->rules('required', 'max:255')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })->resolveUsing(function (string $gtid): string {
                    // Hide GTID when the attendee is known
                    return null !== $this->attendee ? '—' : $gtid;
                }),

            BelongsTo::make('User', 'attendee'),

            MorphTo::make('Attended', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),

            BelongsTo::make('Recorded By', 'recorded', User::class)
                ->help('The user that recorded the swipe'),

            DateTime::make('Time', 'created_at')
                ->sortable(),

            Text::make('Source')
                ->hideFromIndex()
                ->sortable(),

            new Panel('Metadata', $this->metaFields()),
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
            (new AttendanceSourceBreakdown())->width('1/2'),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
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
    public function lenses(Request $request): array
    {
        return [
            (new RecentInactiveUsers())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-attendance');
            }),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new ExportAttendance())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-attendance');
            })->canRun(static function (Request $request): bool {
                return $request->user()->can('read-attendance');
            }),
        ];
    }

    /**
     * Determine if the current user can view the given resource or throw an exception.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
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
    public function authorizedToDelete(Request $request): bool
    {
        return $request instanceof LensRequest ? false : parent::authorizedToDelete($request);
    }
}
