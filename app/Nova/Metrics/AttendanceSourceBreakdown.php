<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Attendance;
use Illuminate\Http\Request;
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
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function calculate(Request $request): PartitionResult
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
                case null:
                    return 'Unknown';
                default:
                    return ucfirst($value);
            }
        });
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'attendance-sources';
    }
}
