<?php

declare(strict_types=1);

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a possible dues payment and what privileges are associated with it.
 *
 * @method static \Illuminate\Database\Eloquent\Builder availableForPurchase() Scopes a query to only packages
 *           available for purchase
 *
 * @property \DateTime $access_end The timestamp when users paid against this DuesPackage no longer have access to
 *                                 systems
 * @property \DateTime $access_start The timestamp when users paid against this DuesPackage first have access to systems
 * @property \DateTime $effective_end The timestamp when the DuesPackage is considered no longer active
 * @property \DateTime $effective_start The timestamp when the DuesPackage is considered newly active
 * @property bool $eligible_for_polo Whether this DuesPackage grants eligibility for a polo
 * @property bool $eligible_for_shirt Whether this DuesPackage grants eligibility for a shirt
 * @property bool $is_active Whether this DuesPackage is considered active
 * @property float $cost the cost of this package
 * @property int $id The database identifier for this DuesPackage
 */
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
     */
    public function scopeAvailableForPurchase(Builder $query): Builder
    {
        return $query->where('available_for_purchase', 1);
    }

    /**
     * Scope a query to only include active DuesPackages.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('effective_start', '<', date('Y-m-d H:i:s'))
            ->where('effective_end', '>', date('Y-m-d H:i:s'));
    }

    /**
     * Scope a query to only include access active DuesPackages.
     */
    public function scopeAccessActive(Builder $query): Builder
    {
        return $query->where('access_start', '<', date('Y-m-d H:i:s'))
            ->where('access_end', '>', date('Y-m-d H:i:s'));
    }

    /**
     * Get the is_active flag for the DuesPackage.
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
