<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

abstract class MajorDemographicsBreakdown extends Partition
{
    /**
     * The name of the field to base the metric on.
     *
     * @var string
     */
    protected $field_name;

    /**
     * Create a new MajorDemographicsBreakdown metric.
     */
    public function __construct(string $field_name)
    {
        parent::__construct();
        $this->field_name = $field_name;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        return $this->result(
            User::active()
                ->with('majors')
                ->get()
                ->map(fn (User $user): string => $user->majors->pluck($this->field_name)->sort()->join('/'))
                ->groupBy(static fn (string $majors): string => strlen($majors) === 0 ? 'none or unknown' : $majors)
                ->map(static fn (Collection $coll): int => $coll->count())
                ->sort()
                ->reverse()
                ->toArray()
        );
    }
}
