<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Payment;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class PaymentsPerDay extends Trend
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays(
            $request,
            Payment::select('payments.updated_at')
                ->where('payments.amount', '>', 0)
                ->where('payments.method', '!=', 'waiver')
                ->where('payments.method', '!=', 'unknown')
                ->whereNull('payments.deleted_at'),
            'payments.updated_at'
        )->showLatestValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int|string,string>
     */
    #[\Override]
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
    #[\Override]
    public function uriKey(): string
    {
        return 'transactions-per-day';
    }
}
