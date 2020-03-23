<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Attendance;
use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class MajorsBreakdown extends Partition
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
        return $this->result(User::active()
            ->with('majors')
            ->get()
            ->map(static function (User $user): string {
                return $user->majors->pluck('whitepages_ou')->sort()->join('/');
            })->groupBy(static function (string $majors): string {
                return $majors;
            })->map->count()
            ->sort()
            ->reverse());
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'attendance-sources';
    }
}
