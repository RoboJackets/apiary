<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator

namespace App\Nova\Metrics;

use App\Models\DuesTransaction;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\ValueResult;

class MemberSince extends TextMetric
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request): ValueResult
    {
        // Same logic as in DashboardController
        $transaction = DuesTransaction::select(
            'dues_transactions.id'
        )
        ->leftJoin('payments', static function (JoinClause $join): void {
            $join->on('dues_transactions.id', '=', 'payable_id')
                 ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                 ->where('payments.amount', '>', 0);
        })
        ->where('user_id', $request->resourceId)
        ->orderBy('payments.updated_at')
        ->first();

        if (null !== $transaction) {
            return $this->result(date('F j, Y', strtotime(
                $transaction->payment()->where('amount', '>', 0)->first()->updated_at->toDateTimeString()
            )));
        }

        return $this->result('n/a');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'member-since';
    }
}
