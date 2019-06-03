<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator

namespace App\Nova\Metrics;

use App\DuesTransaction;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class MemberSince extends Value
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
        // Same logic as in DashboardController
        $transaction = DuesTransaction::paid()->where('user_id', $request->resourceId)->with('package')->first();

        if ($transaction) {
            // The date must be passed in as the prefix, or the non-numeric characters will be stripped and it will be
            // treated as a number. This is ugly but works. See
            // vendor/laravel/nova/resources/js/components/Metrics/Base/ValueMetric.vue line 124.
            return $this->result('')->prefix(date('F j, Y', strtotime($transaction->created_at)));
        }

        return $this->result('n/a');
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'member-since';
    }
}
