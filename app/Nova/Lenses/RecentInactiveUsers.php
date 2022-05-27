<?php

declare(strict_types=1);

namespace App\Nova\Lenses;

use App\Models\Team as AppModelsTeam;
use App\Nova\Event;
use App\Nova\Filters\Attendable;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Team;
use App\Nova\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

/**
 * Shows GTIDs that have recently attended an event but haven't paid dues.
 *
 * @property ?\App\Models\User $attendee The attendee for an event
 */
class RecentInactiveUsers extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>
     */
    public static function query(LensRequest $request, $query): Builder
    {
        return $request->withOrdering(
            $request->withFilters(
                $query->whereDoesntHave('attendee', static function (Builder $q): void {
                    $q->active();
                })
                    ->where('attendable_type', AppModelsTeam::getMorphClassStatic())
                    ->whereBetween('created_at', [now()->subDays(14)->startOfDay(), now()])
                    ->select('gtid', 'attendable_id', 'attendable_type')
                    ->distinct()
            )
        );
    }

    /**
     * Get the fields available to the lens.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('GTID')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })->resolveUsing(function (string $gtid): string {
                    // Hide GTID when the attendee is known
                    return null !== $this->attendee ? '—' : $gtid;
                }),

            BelongsTo::make('User', 'attendee', User::class),

            MorphTo::make('Attended', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),
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
            (new ActiveAttendanceBreakdown())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users') && $request->user()->can('read-attendance');
            }),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [
            new Attendable(false),
        ];
    }

    /**
     * The displayable name of the lens.
     *
     * @var string
     */
    public $name = 'Recent Inactive Attendees, Last Two Weeks';

    /**
     * Get the URI key for the lens.
     */
    public function uriKey(): string
    {
        return 'recent-inactive-users';
    }
}
