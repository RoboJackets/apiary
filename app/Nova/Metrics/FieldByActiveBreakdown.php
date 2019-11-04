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
     *
     * @suppress PhanPossiblyNonClassMethodCall
     */
    public function calculate(Request $request): PartitionResult
    {
        $result = $this->getQuery()
            ->get()
            ->groupBy('is_active')
            ->mapWithKeys(static function (Collection $coll, int $key): array {
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
