<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class ClassStandingsBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Class Standing of Active Members';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        return $this->result(User::active()
            ->with('classStanding')
            ->get()
            ->map(static fn (User $user): string => $user->classStanding->pluck('name')->sort()->join('/'))
            ->groupBy(
                static fn (string $classStandings): string => strlen(
                    $classStandings
                ) === 0 ? 'none or unknown' : ucfirst(
                    $classStandings
                )
            )->map(
                static fn (Collection $coll): int => $coll->count()
            )->sort()
            ->reverse()->toArray());
    }

    /**
     * Get the URI key for the metric.
     */
    #[\Override]
    public function uriKey(): string
    {
        return 'class-standings-breakdown';
    }
}
