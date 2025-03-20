<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class TotalCollections extends Value
{
    /**
     * The element's icon.
     *
     * @var string
     */
    public $icon = 'currency-dollar';

    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Net Dues Revenue';

    /**
     * The help text for the metric.
     *
     * @var string
     */
    public $helpText = 'Total dues revenue collected for this fiscal year, including all payment methods, excluding waivers and processing fees';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        $query = self::query($request->resource, intval($request->resourceId));

        if ($request->range > 0) {
            $query = $query->whereBetween(
                'created_at',
                [
                    now()->subDays($request->range)->startOfDay(),
                    now(),
                ]
            );
        }

        $revenue = $query->first()->revenue;

        $result = $this->result($revenue)->dollars()->allowZeroResult();

        if ($request->range > 0 && $revenue > 0) {
            $result->previous(
                self::query(
                    $request->resource,
                    intval($request->resourceId)
                )->whereBetween(
                    'created_at',
                    [
                        now()->subDays(intval($request->range) * 2)->startOfDay(),
                        now()->subDays(intval($request->range))->startOfDay(),
                    ]
                )->first()->revenue
            );
        } elseif ($revenue > 0 && $request->resource === 'fiscal-years') {
            $previousFiscalYear = FiscalYear::where(
                'ending_year',
                intval(FiscalYear::where('id', $request->resourceId)->sole()->ending_year) - 1
            )->first();

            if ($previousFiscalYear !== null) {
                $result->previous(self::query($request->resource, $previousFiscalYear->id)->first()->revenue);
            }
        }

        return $result;
    }

    private static function query(string $resource, int $resourceId): EloquentBuilder
    {
        return Payment::selectRaw(
            '(coalesce(sum(payments.amount),0) - coalesce(sum(payments.processing_fee),0)) as revenue'
        )
            ->where('payable_type', DuesTransaction::getMorphClassStatic())
            ->where('method', '!=', 'waiver')
            ->whereNull('deleted_at')
            ->whereIn('payable_id', static function (Builder $q) use ($resource, $resourceId): void {
                $q->select('id')
                    ->from('dues_transactions')
                    ->when(
                        $resource === 'fiscal-years',
                        static function (Builder $query, bool $isFiscalYear) use ($resourceId): void {
                            $query
                                ->whereIn(
                                    'dues_package_id',
                                    static function (Builder $query) use ($resourceId): void {
                                        $query->select('id')
                                            ->from('dues_packages')
                                            ->where('fiscal_year_id', $resourceId);
                                    }
                                );
                        },
                        static function (Builder $query) use ($resourceId): void {
                            $query->where('dues_package_id', $resourceId);
                        }
                    )
                    ->whereNull('deleted_at');
            });
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int,string>
     */
    #[\Override]
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
    #[\Override]
    public function uriKey(): string
    {
        return 'total-collections';
    }
}
