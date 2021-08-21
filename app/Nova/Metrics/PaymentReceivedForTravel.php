<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

use App\Models\Travel;
use App\Models\TravelAssignment;
use Illuminate\Database\Query\JoinClause;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class PaymentReceivedForTravel extends Partition
{
    /**
     * The travel model this is displaying (if not on a travel detail page)
     *
     * @var int
     */
    protected $resourceId;

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
            ? 'Payment Received'
            : 'Payment Received for '.Travel::where('id', $this->resourceId)->sole()->name;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $resourceId = $request->resourceId ?? $this->resourceId;

        return $this->result(
            TravelAssignment::selectRaw('IF(ISNULL(payments.id), \'Not Paid\', \'Paid\') as paid')
                ->selectRaw('count(distinct travel_assignments.id) as count')
                ->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('travel_assignments.id', '=', 'payable_id')
                         ->where('payments.amount', '>', 0)
                         ->where('payments.payable_type', TravelAssignment::getMorphClassStatic())
                         ->whereNull('payments.deleted_at');
                })
                ->where('travel_id', $resourceId)
                ->groupBy('paid')
                ->orderByDesc('paid') // sorts "Paid" first
                ->get()
                ->mapWithKeys(static function (object $row): array {
                    // @phpstan-ignore-next-line
                    return [$row->paid => $row->count];
                })
                ->toArray()
        )->colors(  // nova default pie chart colors but mapped to specific series
            [
                'Not Paid' => '#F5573B',
                'Paid' => '#8fc15d',
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
        return -1 === $this->resourceId ? 'payment-received' : 'payment-received-'.$this->resourceId;
    }
}
