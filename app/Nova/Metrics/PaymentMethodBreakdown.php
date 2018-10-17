<?php

namespace App\Nova\Metrics;

use App\Payment;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

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
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->result(Payment::where('payable_type', 'App\DuesTransaction')
            ->whereIn('payable_id', function ($q) use ($request) {
                $q->select('id')
                    ->from('dues_transactions')
                    ->where('dues_package_id', $request->resourceId)
                    ->whereNull('deleted_at');
            })->select('method')
            ->selectRaw('count(payments.id) as aggregate')
            ->groupBy('method')
            ->orderBy('aggregate', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
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
                        $key = 'SquareCash';
                        break;
                }

                return [$key => $item->aggregate];
            })->toArray()
        );
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'payment-method-breakdown';
    }
}
