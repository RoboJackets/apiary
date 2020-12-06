<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

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
     *
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    public function calculate(Request $request): PartitionResult
    {
        return $this->result(
            Payment::where('payable_type', \App\Models\DuesTransaction::class)
                    ->where('amount', '>', 0)
                    ->whereIn('payable_id', static function (Builder $q) use ($request): void {
                        $q->select('id')
                            ->from('dues_transactions')
                            ->where('dues_package_id', $request->resourceId)
                            ->whereNull('deleted_at');
                    })
                    ->select('method')
                    ->selectRaw('count(payments.id) as aggregate')
                    ->groupBy('method')
                    ->orderBy('aggregate', 'desc')
                    ->get()
                    ->mapWithKeys(static function (Payment $item): array {
                        return [Payment::$methods[$item->method] => $item->aggregate];
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
