<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
use App\Attendance;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Illuminate\Database\Eloquent\Builder;

abstract class FieldByActiveBreakdown extends Partition
{
    /**
     * The name of the field to base the metric on.
     *
     * @var string
     */
    protected $field_name;

    /**
     * Create a new LinkedAccountsBreakdown metric.
     *
     * @param string  $field_name
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $field_name)
    {
        $this->field_name = $field_name;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function calculate(Request $request): PartitionResult
    {
        $result = $this->getQuery()
            ->get()
            ->groupBy('is_active')
            ->mapWithKeys(static function (object $coll, int $key): array {
                $keyStr = 1 === $key ? 'Active' : 'Inactive';

                return [$keyStr => $coll->count()];
            })->toArray();

        return $this->result($result);
    }

    protected function getQuery(): Builder
    {
        return User::whereNotNull($this->field_name);
    }
}
