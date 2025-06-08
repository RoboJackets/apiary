<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Attendance;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class AttendanceSourceBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Attendance Sources';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, Attendance::class, 'source')->label(static function (?string $value): string {
            switch ($value) {
                case 'kiosk':
                    return 'Kiosk (unknown type)';
                case 'kiosk-contactless':
                    return 'Kiosk (contactless)';
                case 'kiosk-magstripe':
                    return 'Kiosk (magstripe)';
                case 'manual':
                    return 'Manual entry';
                case 'MyRoboJackets':
                    return 'Swipe/contactless, not kiosk';
                case 'secret-link':
                    return 'Secret link';
                case 'remote-attendance-link':
                    return 'Remote attendance link';
                case 'remote-attendance-link-creation':
                    return 'Remote attendance link creation';
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
        return 'attendance-sources';
    }
}
