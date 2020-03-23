<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
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
    public $name = 'Majors of Active Members';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        // @phan-suppress-next-line PhanPossiblyNonClassMethodCall
        return $this->result(User::active()
            ->with('class_standings')
            ->get()
            ->map(static function (User $user): string {
                return $user->class_standings->pluck('name')->sort()->join('/');
            })->groupBy(static function (string $majors): string {
                return 0 === strlen($majors) ? 'none or unknown' : $majors;
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
