<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\TravelAssignment;
use Illuminate\Database\Query\JoinClause;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class OverdueTripFees extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'currency-dollar';

    /**
     * The help text for the metric.
     *
     * @var string
     */
    public $helpText = 'Total trip fees that have been requested but not paid prior to departure';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        $amount = TravelAssignment::selectRaw('sum(travel.fee_amount) as total_fee_amount')
            ->leftJoin('payments', static function (JoinClause $join): void {
                $join->on('travel_assignments.id', '=', 'payable_id')
                    ->where('payments.amount', '>', 0)
                    ->where('payments.payable_type', TravelAssignment::getMorphClassStatic())
                    ->whereNull('payments.deleted_at');
            })
            ->leftJoin('travel', static function (JoinClause $join): void {
                $join->on('travel_assignments.travel_id', '=', 'travel.id')
                    ->whereNull('travel.deleted_at');
            })
            ->whereNull('payments.id')
            ->whereDate('travel.departure_date', '<=', now())
            ->get()
            ->toArray();

        return $this->result($amount[0]['total_fee_amount'])->dollars()->allowZeroResult();
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'overdue-trip-fees';
    }
}
