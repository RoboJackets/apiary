<?php

declare(strict_types=1);

namespace App\Nova\Lenses;

use App\Nova\Event;
use App\Nova\Filters\Attendable;
use App\Nova\Team;
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
 * @property ?\App\User $attendee The attendee for an event
 */
class RecentInactiveUsers extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public static function query(LensRequest $request, $query): Builder
    {
        return $request->withOrdering(
            $request->withFilters(
                $query->whereDoesntHave('attendee', static function (Builder $q): void {
                    $q->active();
                })
                    ->where('attendable_type', \App\Models\Team::class)
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

            BelongsTo::make('User', 'attendee', \App\Nova\User::class),

            MorphTo::make('Attended', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),
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
     * Get the URI key for the lens.
     */
    public function uriKey(): string
    {
        return 'recent-inactive-users';
    }
}
