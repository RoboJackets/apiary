<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Travel;
use App\Models\TravelAssignment;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class TravelAuthorityRequestReceivedForTravel extends Partition
{
    /**
     * Labels for the tar_recieved field, conveniently indexed by the numeric value.
     *
     * @phan-read-only
     *
     * @var array<string>
     */
    private static $partition_labels = [
        'Not Received',
        'Received',
    ];

    public function __construct(private readonly int $resourceId = -1)
    {
        parent::__construct();
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return $this->resourceId === -1 ? 'Forms Received' : 'Forms Received for '.Travel::where(
            'id',
            $this->resourceId
        )->sole()->name;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $resourceId = $request->resourceId ?? $this->resourceId;

        return $this->result(
            TravelAssignment::select('tar_received')
                ->selectRaw('count(distinct id) as count')
                ->where('travel_id', $resourceId)
                ->groupBy('tar_received')
                ->orderByDesc('tar_received')
                ->get()
                ->mapWithKeys(
                    static fn (object $row): array => [self::$partition_labels[$row->tar_received] => $row->count]
                )
                ->toArray()
        )->colors(  // nova default pie chart colors but mapped to specific series
            [
                'Not Received' => '#F5573B',
                'Received' => '#8fc15d',
            ]
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return $this->resourceId === -1 ? 'forms-received' : 'forms-received-'.$this->resourceId;
    }
}
