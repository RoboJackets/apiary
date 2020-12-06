<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class PrimaryAffiliationBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Primary Affiliation with GT of Active Members';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        return $this->count($request, User::active(), 'primary_affiliation')
            ->label(static function (?string $value): string {
                switch ($value) {
                    case null:
                        return 'Unknown';
                    default:
                        return ucfirst($value);
                }
            });
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'primary-affiliation';
    }
}
