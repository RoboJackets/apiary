<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class PaymentMethodBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Payment Methods';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): PartitionResult
    {
        return $this->result(
            Payment::where('payable_type', DuesTransaction::getMorphClassStatic())
                    ->where('amount', '>', 0)
                    ->whereIn('payable_id', static function (Builder $query) use ($request): void {
                        $query->select('id')
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
                    })
                    ->select('method')
                    ->selectRaw('count(payments.id) as count')
                    ->groupBy('method')
                    ->orderByDesc('count')
                    ->get()
                    ->mapWithKeys(static function (object $row): array {
                        return [Payment::$methods[$row->method] => $row->count];
                    })->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'payment-method-breakdown';
    }
}
