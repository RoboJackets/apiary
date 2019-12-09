<?php

declare(strict_types=1);

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class DuesPackage extends Model
{
    use Actionable;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'is_active',
        'is_access_active',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'effective_start',
        'effective_end',
        'access_start',
        'access_end',
    ];

    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function duesTransactions(): HasMany
    {
        return $this->hasMany(\App\DuesTransaction::class);
    }

    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(\App\DuesTransaction::class);
    }

    /**
     * Scope a query to only include DuesPackages available for purchase.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableForPurchase(Builder $query): Builder
    {
        return $query->where('available_for_purchase', 1);
    }

    /**
     * Scope a query to only include active DuesPackages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('effective_start', '<', date('Y-m-d H:i:s'))
            ->where('effective_end', '>', date('Y-m-d H:i:s'));
    }

    /**
     * Scope a query to only include access active DuesPackages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessActive(Builder $query): Builder
    {
        return $query->where('access_start', '<', date('Y-m-d H:i:s'))
            ->where('access_end', '>', date('Y-m-d H:i:s'));
    }

    /**
     * Get the is_active flag for the DuesPackage.
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        $now = new DateTime();
        $start = $this->effective_start;
        $end = $this->effective_end;

        return ($start <= $now) && ($end >= $now);
    }

    /**
     * Get the is_active flag for the DuesPackage.
     *
     * @return bool
     */
    public function getIsAccessActiveAttribute(): bool
    {
        $now = new DateTime();
        $start = $this->access_start;
        $end = $this->access_end;

        return ($start <= $now) && ($end >= $now);
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'transactions' => 'dues-transactions',
        ];
    }
}
