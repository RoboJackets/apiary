<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class DuesTransactionPaymentStatus extends BooleanFilter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Payment Status';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value['pending']) {
            return $query->leftJoin('payments', function ($j) {
                $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', \App\DuesTransaction::class)
                    ->where('payments.deleted_at', '=', null);
            })
            ->join('dues_packages', 'dues_packages.id', '=', 'dues_transactions.dues_package_id')
            ->groupBy('dues_transactions.id', 'dues_transactions.dues_package_id', 'dues_packages.cost')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) < dues_packages.cost');
        } else {
            return $query;
        }
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Only Pending' => 'pending',
        ];
    }
}
