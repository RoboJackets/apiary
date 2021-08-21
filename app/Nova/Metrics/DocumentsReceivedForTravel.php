<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

use App\Models\Travel;
use App\Models\TravelAssignment;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class DocumentsReceivedForTravel extends Partition
{
    /**
     * The travel model this is displaying (if not on a travel detail page).
     *
     * @var int
     */
    protected $resourceId;

    /**
     * Labels for the document_recieved field, conveniently indexed by the numeric value.
     *
     * @var array<string>
     */
    private static $partition_labels = [
        'Not Received',
        'Received',
    ];

    public function __construct(int $resourceId = -1)
    {
        parent::__construct();
        $this->resourceId = $resourceId;
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return -1 === $this->resourceId
            ? 'Documents Received'
            : 'Documents Received for '.Travel::where('id', $this->resourceId)->sole()->name;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $resourceId = $request->resourceId ?? $this->resourceId;

        return $this->result(
            TravelAssignment::select('documents_received')
                ->selectRaw('count(distinct id) as count')
                ->where('travel_id', $resourceId)
                ->groupBy('documents_received')
                ->orderByDesc('documents_received')
                ->get()
                ->mapWithKeys(static function (object $row): array {
                    return [self::$partition_labels[$row->documents_received] => $row->count];
                })
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
     *
     * @return string
     */
    public function uriKey()
    {
        return -1 === $this->resourceId ? 'documents-received' : 'documents-received-'.$this->resourceId;
    }
}
