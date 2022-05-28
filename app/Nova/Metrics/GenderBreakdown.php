<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class GenderBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Gender of Active Members';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, User::active(), 'gender')->label(static function (?string $value): string {
            switch ($value) {
                // Original enum values in resources/js/components/dues/Demographics.vue
                case 'nonbinary':
                    return 'Non-binary or gender-queer';
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
        return 'gender-breakdown';
    }
}
