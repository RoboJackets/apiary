<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get all of the owning payable models
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Payment model.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'recorded_by');
    }

    /**
     * Get the DuesTransaction
     */
    public function duesTransaction()
    {
        return $this->hasOne('App\DuesTransaction');
    }
}
