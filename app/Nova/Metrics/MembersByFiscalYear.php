<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesTransaction;
use Illuminate\Database\Query\JoinClause;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class MembersByFiscalYear extends Trend
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): TrendResult
    {
        return (new TrendResult())
        ->trend(
            DuesTransaction::selectRaw(
                'ending_year, count(distinct user_id) as member_count'
            )
            ->leftJoin('payments', static function (JoinClause $join): void {
                $join->on('dues_transactions.id', '=', 'payable_id')
                     ->where('payments.amount', '>', 0)
                     ->where('payments.payable_type', DuesTransaction::getMorphClassStatic());
            })
            ->leftJoin(
                'dues_packages',
                'dues_transactions.dues_package_id',
                '=',
                'dues_packages.id'
            )
            ->leftJoin(
                'fiscal_years',
                'dues_packages.fiscal_year_id',
                '=',
                'fiscal_years.id'
            )
            ->groupBy('ending_year')
            ->orderBy('ending_year')
            ->get()
            ->mapWithKeys(
                static function (object $record): array {
                    return [$record->ending_year => $record->member_count];
                }
            )
            ->toArray()
        )
        ->showLatestValue();
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'members-by-fiscal-year';
    }
}
