<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

abstract class MajorDemographicsBreakdown extends Partition
{
    /**
     * The name of the field to base the metric on.
     */
    protected string $field_name;

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
        // @phan-suppress-next-line PhanPossiblyNonClassMethodCall
        return $this->result(User::active()
            ->with('majors')
            ->get()
            ->map(function (User $user): string {
                return $user->majors->pluck($this->field_name)->sort()->join('/');
            })->groupBy(static function (string $majors): string {
                return 0 === strlen($majors) ? 'none or unknown' : $majors;
            })->map(static function (Collection $coll): int {
                return $coll->count();
            })->sort()
            ->reverse()->toArray());
    }
}
