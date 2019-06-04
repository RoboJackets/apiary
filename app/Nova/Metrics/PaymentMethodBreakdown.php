<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Payment;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as Eloquent;

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
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function calculate(Request $request): PartitionResult
    {
        return $this->result(
            Payment::where('payable_type', 'App\DuesTransaction')
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
                    ->mapWithKeys(static function ($item): array {
                        $key = $item->method;
                        switch ($item->method) {
                            case 'cash':
                                $key = 'Cash';
                                break;
                            case 'check':
                                $key = 'Check';
                                break;
                            case 'swipe':
                                $key = 'Swiped Card';
                                break;
                            case 'square':
                                $key = 'Square (Online)';
                                break;
                            case 'squarecash':
                                $key = 'Square Cash';
                                break;
                        }

                        return [$key => $item->aggregate];
                    })->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'payment-method-breakdown';
    }
}
