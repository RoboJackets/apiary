<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DuesTransaction extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status'];
    

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'status'
    ];

    /**
     * Get the Payment associated with the DuesTransaction model.
     */
    public function payment()
    {
        return $this->morphMany('App\Payment', 'payable');
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

    /**
     * Alias the generalize form of the Transaction for Polymorphic Reasons
     */
    public function for()
    {
        return $this->package();
    }


    /**
     * Get the status flag for the Transaction.
     *
     * @return bool
     */
    public function getStatusAttribute()
    {
        if ($this->payment->count() > 0) {
            return "paid";
        } else {
            return "pending";
        }
    }

    /**
     * Scope a query to only include pending transactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->doesntHave('payment');
    }
}
