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
            ->map(static function (User $user): string {
                return $user->classStanding->pluck('name')->sort()->join('/');
            })->groupBy(static function (string $classStandings): string {
                return 0 === strlen($classStandings) ? 'none or unknown' : ucfirst($classStandings);
            })->map(static function (Collection $coll): int {
                return $coll->count();
            })->sort()
            ->reverse()->toArray());
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'class-standings-breakdown';
    }
}
