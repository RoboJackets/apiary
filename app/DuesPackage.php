<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DuesPackage extends Model
{
    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function transactions()
    {
        return $this->hasMany('App\DuesTransaction');
    }
}
