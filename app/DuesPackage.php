<?php

namespace App;

use Laravel\Nova\Actions\Actionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DuesPackage extends Model
{
    use Actionable;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_active'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'effective_start',
        'effective_end',
    ];

    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function transactions()
    {
        return $this->hasMany(\App\DuesTransaction::class);
    }

    /**
     * Scope a query to only include DuesPackages available for purchase.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableForPurchase($query)
    {
        return $query->where('available_for_purchase', 1);
    }

    /**
     * Scope a query to only include active DuesPackages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereDate('effective_start', '<=', date('Y-m-d'))
            ->whereDate('effective_end', '>=', date('Y-m-d'));
    }

    /**
     * Get the is_active flag for the DuesPackage.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        $now = new \DateTime();
        $start = new \DateTime($this->effective_start);
        $end = new \DateTime($this->effective_end);

        return ($start <= $now) && ($end >= $now);
    }
}
