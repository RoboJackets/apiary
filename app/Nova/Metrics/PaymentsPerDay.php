<?php declare(strict_types = 1);

namespace App\Nova\Metrics;

use App\Payment;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\ValueResult;

class PaymentsPerDay extends Trend
{
    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Laravel\Nova\Metrics\ValueResult
     */
    public function calculate(Request $request): ValueResult
    {
        return $this->countByDays($request, Payment::class)->showLatestValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int|string,string>
     */
    public function ranges(): array
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            90 => '90 Days',
            365 => '365 Days',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'transactions-per-day';
    }
}
