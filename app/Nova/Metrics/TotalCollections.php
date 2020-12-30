<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class TotalCollections extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        $query = Payment::where('payable_type', DuesTransaction::getMorphClassStatic())
            ->whereIn('payable_id', static function (Builder $q) use ($request): void {
                $q->select('id')
                    ->from('dues_transactions')
                    ->when(
                        'fiscal-years' === $request->resource,
                        static function (Builder $query, bool $isFiscalYear) use ($request): void {
                            $query
                                ->whereIn(
                                    'dues_package_id',
                                    DuesPackage::where(
                                        'fiscal_year_id',
                                        $request->resourceId
                                    )
                                    ->get()
                                    ->map(static function (DuesPackage $package): int {
                                        return $package->id;
                                    })
                                );
                        },
                        static function (Builder $query) use ($request): void {
                            $query->where('dues_package_id', $request->resourceId);
                        }
                    )
                    ->whereNull('deleted_at');
            });
        if ($request->range > 0) {
            $query = $query->whereBetween('created_at', [now()->subDays($request->range)->startOfDay(), now()]);
        }

        return $this->result($query->sum('amount') - $query->sum('processing_fee'))->dollars()->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    public function ranges(): array
    {
        return [
            -1 => 'All',
            7 => '7 Days',
            14 => '14 Days',
            30 => '30 Days',
            60 => '60 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'total-collections';
    }
}
