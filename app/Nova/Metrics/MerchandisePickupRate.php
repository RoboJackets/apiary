<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesTransaction;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class MerchandisePickupRate extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Pickup Rate';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->result(
            DB::table('dues_transactions')
                ->selectRaw('IF(ISNULL(provided_at), \'Not Picked Up\', \'Picked Up\') as provided')
                ->selectRaw('count(distinct dues_transactions.user_id) as count')
                ->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('dues_transactions.id', '=', 'payable_id')
                        ->where('payments.amount', '>', 0)
                        ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                        ->whereNull('payments.deleted_at');
                })
                ->leftJoin(
                    'dues_transaction_merchandise',
                    'dues_transactions.id',
                    '=',
                    'dues_transaction_merchandise.dues_transaction_id'
                )
                ->whereNotNull('payments.id')
                ->whereNull('payments.deleted_at')
                ->whereNull('dues_transactions.deleted_at')
                ->where('merchandise_id', $request->resourceId)
                ->groupBy('provided')
                ->orderByDesc('provided') // sorts "Picked Up" first
                ->get()
                ->mapWithKeys(
                    static fn (object $record): array => [$record->provided => $record->count]
                )
                ->toArray()
        )->colors(  // nova default pie chart colors but mapped to specific series
            [
                'Not Picked Up' => '#F5573B',
                'Picked Up' => '#8fc15d',
            ]
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'pickup-rate';
    }
}
