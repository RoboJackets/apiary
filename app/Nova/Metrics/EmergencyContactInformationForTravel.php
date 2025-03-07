<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference

namespace App\Nova\Metrics;

use App\Models\Travel;
use App\Models\TravelAssignment;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class EmergencyContactInformationForTravel extends Partition
{
    public function __construct(private readonly int $resourceId = -1)
    {
        parent::__construct();
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return $this->resourceId === -1 ?
            'Emergency Contact Information' :
            'Emergency Contact Information for '.Travel::where('id', '=', $this->resourceId)->sole()->name;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $trip = Travel::with('assignments.user')->where('id', '=', $request->resourceId ?? $this->resourceId)->sole();

        $values = [
            'Submitted' => 0,
            'Not Submitted' => 0,
        ];

        $trip->assignments->each(static function (TravelAssignment $assignment) use (&$values): void {
            if ($assignment->user->has_emergency_contact_information) {
                $values['Submitted'] += 1;
            } else {
                $values['Not Submitted'] += 1;
            }
        });

        return $this->result(array_filter($values, static fn (int $count): bool => $count > 0))->colors(
            [
                'Not Submitted' => '#F5573B',
                'Submitted' => '#8fc15d',
            ]
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return $this->resourceId === -1 ? 'emergency-contact' : 'emergency-contact-'.$this->resourceId;
    }
}
