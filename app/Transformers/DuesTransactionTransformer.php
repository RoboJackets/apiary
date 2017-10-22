<?php

namespace App\Transformers;

use App\DuesTransaction;
use League\Fractal\TransformerAbstract;

class DuesTransactionTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "payment",
        "package",
        "user"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(DuesTransaction $transact)
    {
        return [
            "id" => $transact->id,
            "received_polo" => $transact->received_polo,
            "received_shirt" => $transact->received_shirt,
            "dues_package_id" => $transact->dues_package_id,
            "user_id" => $transact->user_id,
            "status" => $transact->status
        ];
    }

    public function includePayment(DuesTransaction $transact)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-payments')) {
            return $this->item($transact->payment, new PaymentTransformer());
        } else {
            return null;
        }
    }
    
    public function includePackage(DuesTransaction $transact)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-dues-packages')) {
            return $this->item($transact->package, new DuesPackageTransformer());
        } else {
            return null;
        }
    }
    
    public function includeUser(DuesTransaction $transact)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-users')) {
            return $this->item($transact->user, new UserTransformer());
        } else {
            return null;
        }
    }
}
