<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DuesTransaction extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the Payment associated with the DuesTransaction model.
     */
    public function payment()
    {
        return $this->belongsTo('App\Payment');
    }

    /**
     * Get the DuesPackage associated with the DuesTransaction model.
     */
    public function package()
    {
        return $this->belongsTo('App\DuesPackage', 'dues_package_id');
    }

    /**
     * Get the User associated with the DuesTransaction model.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
