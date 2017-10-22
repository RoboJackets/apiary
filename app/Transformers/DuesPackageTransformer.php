<?php

namespace App\Transformers;

use App\DuesPackage;
use League\Fractal\TransformerAbstract;

class DuesPackageTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "transactions"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(DuesPackage $package)
    {
        return [
            "id" => (int) $package->id,
            "name" => $package->name,
            "eligible_for_shirt" => $package->eligible_for_shirt,
            "eligible_for_polo" => $package->eligible_for_polo,
            "effective_start" => $package->effective_start,
            "effective_end" => $package->effective_end,
            "cost" => $package->cost,
            "available_for_purchase" => $package->available_for_purchase,
            "is_active" => $package->is_active
        ];
    }

    public function includeTransactions(DuesPackage $package)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-dues-transactions')) {
            return $this->collection($package->transactions, new DuesTransactionTransformer());
        } else {
            return null;
        }
    }

}
