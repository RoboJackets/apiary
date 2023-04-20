<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesTransaction;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class MerchandiseSelections extends Partition
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->result(
            DuesTransaction::select('merchandise.name as merchandise_name')
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
                ->leftJoin(
                    'merchandise',
                    'dues_transaction_merchandise.merchandise_id',
                    '=',
                    'merchandise.id'
                )
                ->whereNotNull('payments.id')
                ->whereNotNull('merchandise.id')
                ->whereNull('payments.deleted_at')
                ->whereNull('dues_transactions.deleted_at')
                ->when(
                    $request->resource === 'fiscal-years',
                    static function (EloquentBuilder $query, bool $isFiscalYear) use ($request): void {
                        $query
                            ->whereIn(
                                'dues_package_id',
                                static function (QueryBuilder $query) use ($request): void {
                                    $query->select('id')
                                        ->from('dues_packages')
                                        ->where('fiscal_year_id', $request->resourceId);
                                }
                            );
                    },
                    static function (EloquentBuilder $query) use ($request): void {
                        $query->where('dues_package_id', $request->resourceId);
                    }
                )
                ->groupBy('merchandise_name')
                ->orderByDesc('count')
                ->get()
                ->mapWithKeys(
                    static fn (object $record): array => [$record->merchandise_name => $record->count]
                )
                ->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'merchandise-selections';
    }
}
