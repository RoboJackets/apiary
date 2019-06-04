<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

namespace App\Nova\Lenses;

use App\Nova\Team;
use App\Nova\Event;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use App\Nova\Filters\Attendable;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\LensRequest;

class RecentInactiveUsers extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param \Illuminate\Database\Eloquent\Builder  $query
     *
     * @return Builder
     */
    public static function query(LensRequest $request, $query): Builder
    {
        return $request->withOrdering(
            $request->withFilters(
                $query->whereDoesntHave('attendee', static function (Builder $q): void {
                    $q->active();
                })
                    ->where('attendable_type', 'App\Team')
                    ->whereBetween('created_at', [now()->subDays(14)->startOfDay(), now()])
                    ->select('gtid', 'attendable_id', 'attendable_type')
                    ->distinct()
            )
        );
    }

    /**
     * Get the fields available to the lens.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('GTID')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                })->resolveUsing(function ($gtid) {
                    // Hide GTID when the attendee is known
                    return $this->attendee ? '—' : $gtid;
                }),

            BelongsTo::make('User', 'attendee', 'App\Nova\User'),

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
     * @param \Illuminate\Http\Request  $request
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
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'recent-inactive-users';
    }
}
