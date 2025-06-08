<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class CreateReasonBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'User Create Reason';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, User::class, 'create_reason')->label(static function (?string $value): string {
            switch ($value) {
                case 'phpunit':
                    return 'PHPUnit';
                case 'cas_login':
                    return 'CAS login';
                case 'factory':
                    return 'Database\Factories\UserFactory';
                case 'nova_action':
                    return 'Nova action';
                case 'attendance':
                    return 'Attendance';
                case 'historical_dues_import':
                    return 'Historical dues import';
                    // This isn't created by code, but a lot of records in prod have this.
                case 'buzzapi_job':
                    return 'BuzzAPI job';
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
    #[\Override]
    public function uriKey(): string
    {
        return 'create-reasons';
    }
}
