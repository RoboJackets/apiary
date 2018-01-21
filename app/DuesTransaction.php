<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class DuesTransaction extends Model
{
    use SoftDeletes;
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status', 'swag_polo_status', 'swag_shirt_status'];
    

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
        if (!$this->package->is_active) {
            return "expired";
        } elseif ($this->payment->count() == 0) {
            return "pending";
        } elseif ($this->payment->sum('amount') < $this->getPayableAmount()) {
            return "pending";
        } else {
            return "paid";
        }
    }

    /**
     * Get the swag polo status attribute for the Transaction
     *
     * @return string
     */
    public function getSwagPoloStatusAttribute()
    {
        if ($this->package->eligible_for_polo && $this->swag_polo_provided == null) {
            return "Not Picked Up";
        } elseif ($this->package->eligible_for_polo && $this->swag_polo_provided != null) {
            return "Picked Up";
        } else {
            return "Not Eligible";
        }
    }

    /**
     * Get the swag shirt status attribute for the Transaction
     *
     * @return string
     */
    public function getSwagShirtStatusAttribute()
    {
        if ($this->package->eligible_for_shirt && $this->swag_shirt_provided == null) {
            return "Not Picked Up";
        } elseif ($this->package->eligible_for_shirt && $this->swag_shirt_provided != null) {
            return "Picked Up";
        } else {
            return "Not Eligible";
        }
    }

    /**
     * Scope a query to only include pending transactions.
     * Pending defined as no payments, or payments that do not sum to payable amount
     * for a currently active DuesPackage
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->current()->unpaid();
    }
    
    /**
     * Scope a query to only include swag-pending transactions.
     * Swag-pending defined as a paid transaction that has not provided shirt/polo
     * Note that you can't just chain the paid() scope to this because it breaks the joins
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingSwag($query)
    {
        return $query->select("dues_transactions.*", "dues_packages.eligible_for_shirt",
            "dues_packages.eligible_for_polo", DB::raw("COALESCE(SUM(payments.amount),0.00) AS 'amountPaid'"))
            ->join('dues_packages', function ($j) {
                $j->on('dues_packages.id', '=', 'dues_transactions.dues_package_id')
                ->where(function ($q) {
                    $q->where('dues_packages.eligible_for_shirt', '=', true)
                        ->where('dues_transactions.swag_shirt_provided', '=', null)
                        ->orWhere(function ($q) {
                            $q->where('dues_packages.eligible_for_polo', '=', true)
                                ->where('dues_transactions.swag_polo_provided', '=', null);
                        });
                });
            })
            ->leftJoin('payments', function ($j) {
                $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', "App\\DuesTransaction")
                    ->where('payments.deleted_at', '=', null);
            })
            ->groupBy("dues_transactions.id", "dues_transactions.dues_package_id", "dues_packages.cost")
            ->havingRaw("amountPaid >= dues_packages.cost");
    }

    /**
     * Scope a query to only include paid transactions
     * Paid defined as one or more payments whose total is equal to the payable amount
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        $query = $query->select("dues_transactions.*",
            DB::raw("COALESCE(SUM(payments.amount),0.00) AS 'amountPaid'"))
            ->leftJoin('payments', function ($j) {
                $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', "App\\DuesTransaction")
                    ->where('payments.deleted_at', '=', null);
            })
            ->join('dues_packages', 'dues_packages.id', '=', 'dues_transactions.dues_package_id')
            ->groupBy("dues_transactions.id", "dues_transactions.dues_package_id", "dues_packages.cost")
            ->havingRaw("amountPaid >= dues_packages.cost");
        return $query;
    }

    /**
     * Scope a query to only include unpaid transactions
     * Unpaid defined as zero or more payments that are less than the payable amount
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->select("dues_transactions.*",
                DB::raw("COALESCE(SUM(payments.amount),0.00) AS 'amountPaid'"))
            ->leftJoin('payments', function ($j) {
                $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', "App\\DuesTransaction")
                    ->where('payments.deleted_at', '=', null);
            })
            ->join('dues_packages', 'dues_packages.id', '=', 'dues_transactions.dues_package_id')
            ->groupBy("dues_transactions.id", "dues_transactions.dues_package_id", "dues_packages.cost")
            ->havingRaw("amountPaid < dues_packages.cost");
    }

    /**
     * Scope a query to only include current transactions.
     * Current defined as belonging to an active DuesPackage
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->whereHas('package', function ($q) {
            $q->active();
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
