<?php

namespace App\Transformers;

use App\Payment;
use League\Fractal\TransformerAbstract;
use Auth;

class PaymentTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "payable",
        "user"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Payment $payment)
    {
        return [
            "id" => $payment->id,
            "payable_id" => $payment->payable_id,
            "payable_type" => $payment->payable_type,
            "amount" => $payment->amount,
            "method" => $payment->method,
            "recorded_by" => $payment->recorded_by
        ];
    }

    public function includePayable(Payment $payment)
    {
        $authUser = Auth::user();
        if (is_a($payment->payable, "DuesTransaction") && $authUser->can('read-dues-transactions')) {
            return $this->item($payment->payable, new DuesTransactionTransformer());
        } elseif (is_a($payment->payable, "Event") && $authUser->can('read-events')) {
            return $this->item($payment->payable, new EventTransformer());
        } else {
            return null;
        }
    }

    public function includeUser(Payment $payment)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-users')) {
            return $this->item($payment->user, new UserTransformer());
        } else {
            return null;
        }
    }
}
