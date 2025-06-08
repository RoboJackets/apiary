<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Travel;
use App\Models\TravelAssignment;
use Illuminate\Database\Query\JoinClause;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class PaymentReceivedForTravel extends Partition
{
    /**
     * The travel model this is displaying (if not on a travel detail page).
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
     */
    #[\Override]
    public function name(): string
    {
        return $this->resourceId === -1 ? 'Payment Received' : 'Payment Received for '.Travel::where(
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
                ->mapWithKeys(static fn (object $row): array => [$row->paid => $row->count])
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
     */
    #[\Override]
    public function uriKey(): string
    {
        return $this->resourceId === -1 ? 'payment-received' : 'payment-received-'.$this->resourceId;
    }
}
