<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova\Metrics;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

abstract class FieldByActiveBreakdown extends Partition
{
    /**
     * The name of the field to base the metric on.
     *
     * @var string
     */
    protected $field_name;

    /**
     * True to use the is_access_active instead of is_active value.
     *
     * @var bool
     */
    protected $use_access_active;

    /**
     * Create a new FieldByActiveBreakdown metric.
     *
     * @param string  $field_name
     * @param bool  $use_access_active
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $field_name, bool $use_access_active = false)
    {
        $this->field_name = $field_name;
        $this->use_access_active = $use_access_active;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     *
     * @suppress PhanPossiblyNonClassMethodCall
     */
    public function calculate(Request $request): PartitionResult
    {
        $result = $this->getQuery()
            ->get()
            ->groupBy($this->use_access_active ? 'is_access_active' : 'is_active')
            ->mapWithKeys(function (Collection $coll, int $key): array {
                $keyStr = 1 === $key ? 'Active' : 'Inactive';
                if ($this->use_access_active) {
                    $keyStr = 'Access '.$keyStr;
                }

                return [$keyStr => $coll->count()];
            })->sortKeys()->toArray();

        return $this->result($result);
    }

    protected function getQuery(): Builder
    {
        return User::whereNotNull($this->field_name);
    }
}
