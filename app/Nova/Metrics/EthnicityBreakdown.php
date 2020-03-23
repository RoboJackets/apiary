<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\User;
use Illuminate\Http\Request;
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
    public function calculate(Request $request): PartitionResult
    {
        return $this->count($request, User::class, 'gender')->label(static function (?string $value): string {
            switch ($value) {
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
                case null:
                    return 'Unknown'
                default:
                    // @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal
                    return ucfirst($value);
            }
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
