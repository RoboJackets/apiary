<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Payment;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class PaymentsPerDay extends Trend
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): TrendResult
    {
        return $this->countByDays($request, Payment::class)->showLatestValue()->allowZeroResult();
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
        ];
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'transactions-per-day';
    }
}
