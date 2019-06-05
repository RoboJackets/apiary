<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Payment;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Illuminate\Database\Query\Builder;

class TotalCollections extends Value
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
        $query = Payment::where('payable_type', 'App\\DuesTransaction')
            ->whereIn('payable_id', static function (Builder $q) use ($request): void {
                $q->select('id')
                    ->from('dues_transactions')
                    ->where('dues_package_id', $request->resourceId)
                    ->whereNull('deleted_at');
            });
        if ($request->range > 0) {
            $query = $query->whereBetween('created_at', [now()->subDays($request->range)->startOfDay(), now()]);
        }

        return $this->result($query->sum('amount'))->dollars();
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
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'total-collections';
    }
}
