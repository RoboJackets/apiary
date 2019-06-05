<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

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
    public static $model = \App\Attendance::class;

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Attendance';
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel(): string
    {
        return 'Attendance Record';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['recorded', 'attendee'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<mixed>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('GTID')
                ->sortable()
                ->rules('required', 'max:255')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                })->resolveUsing(function (string $gtid): string {
                    // Hide GTID when the attendee is known
                    return $this->attendee ? '—' : $gtid;
                }),

            BelongsTo::make('User', 'attendee'),

            MorphTo::make('Attended', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),

            BelongsTo::make('Recorded By', 'recorded', \App\Nova\User::class)
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
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
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
     * @param \Illuminate\Http\Request  $request
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
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [];
    }

    /**
     * Determine if the current user can view the given resource or throw an exception.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return void
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
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return bool
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
     * @param \Illuminate\Http\Request  $request
     *
     * @return void
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
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request): bool
    {
        return $request instanceof LensRequest ? false : parent::authorizedToDelete($request);
    }
}
