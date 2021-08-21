<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class TransactionsByDuesPackage extends Partition
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $fiscalYearId = $request->resourceId
            ?? FiscalYear::orderByDesc('ending_year')->whereHas('packages.transactions.payment')->first()->id;

        return $this->result(
            DB::table('dues_transactions')
                ->selectRaw(
                    'dues_packages.name as name'
                )
                ->selectRaw(
                    'count(distinct dues_transactions.id) as count'
                )
                ->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('dues_transactions.id', '=', 'payable_id')
                         ->where('payments.amount', '>', 0)
                         ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                         ->whereNull('payments.deleted_at');
                })
                ->leftJoin(
                    'dues_packages',
                    'dues_transactions.dues_package_id',
                    '=',
                    'dues_packages.id'
                )
                ->where('fiscal_year_id', $fiscalYearId)
                ->whereNotNull('payments.id')
                ->whereNull('payments.deleted_at')
                ->whereNull('dues_transactions.deleted_at')
                ->groupBy('name')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(
                    static function (object $record): array {
                        return [$record->name => $record->count];
                    }
                )
                ->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'transactions-by-dues-package';
    }
}
