<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dues extends Model
{
    /**
     * Get the payment associated with the Dues model.
     */
    public function payment()
    {
        return $this->hasOne('App\Payment');
    }
}
