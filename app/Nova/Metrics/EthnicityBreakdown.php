<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class EthnicityBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Ethnicity of Active Members';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, User::active(), 'ethnicity')->label(static function (?string $value): string {
            if ($value === null || $value === '') {
                return 'Unknown';
            }

            return collect(explode(',', $value))->map(static function (string $item): string {
                switch ($item) {
                    // Original enum values in resources/js/components/dues/Demographics.vue
                    case 'white':
                        return 'White/Caucasian';
                    case 'black':
                        return 'Black or African American';
                    case 'native':
                        return 'Native American';
                    case 'islander':
                        return 'Native Hawaiian and Other Pacific Islander';
                    case 'none':
                        return 'Prefer not to respond';
                    default:
                        return ucfirst($item);
                }
            })->join(', ');
        });
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'ethnicity-breakdown';
    }
}
