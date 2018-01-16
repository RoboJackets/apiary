<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DuesTransaction extends Model
{
    use SoftDeletes;
    
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
        if ($this->payment->count() == 0) {
            return "pending";
        } elseif ($this->payment->sum('amount') < $this->getPayableAmount()) {
            return "pending";
        } else {
            return "paid";
        }
    }

    /**
     * Scope a query to only include pending transactions.
     * Pending defined as no payments, or payments that do not sum to payable amount
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->doesntHave('payment')
            ->orWhereHas('payment', function ($q) {
                $q->where('amount', '=', 0);
            });
    }

    /**
     * Get the Payable amount
     */
    public function getPayableAmount()
    {
        return ($this->package->cost) ?: null;
    }
}
