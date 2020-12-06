<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a possible dues payment and what privileges are associated with it.
 *
 * @method static \Illuminate\Database\Eloquent\Builder availableForPurchase()
 * @method static \Illuminate\Database\Query\Builder|DuesPackage onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|DuesPackage withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|DuesPackage withTrashed()
 * @method static Builder|DuesPackage accessActive()
 * @method static Builder|DuesPackage active()
 * @method static Builder|DuesPackage newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static Builder|DuesPackage query()
 * @method static Builder|DuesPackage whereAccessEnd($value)
 * @method static Builder|DuesPackage whereAccessStart($value)
 * @method static Builder|DuesPackage whereAvailableForPurchase($value)
 * @method static Builder|DuesPackage whereCost($value)
 * @method static Builder|DuesPackage whereCreatedAt($value)
 * @method static Builder|DuesPackage whereDeletedAt($value)
 * @method static Builder|DuesPackage whereEffectiveEnd($value)
 * @method static Builder|DuesPackage whereEffectiveStart($value)
 * @method static Builder|DuesPackage whereEligibleForPolo($value)
 * @method static Builder|DuesPackage whereEligibleForShirt($value)
 * @method static Builder|DuesPackage whereId($value)
 * @method static Builder|DuesPackage whereName($value)
 * @method static Builder|DuesPackage whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Carbon\Carbon $access_end The timestamp when users paid against this DuesPackage no longer have access to
 * @property \Carbon\Carbon $access_start The timestamp when users paid against this DuesPackage first are access active
 * @property \Carbon\Carbon $effective_end The timestamp when the DuesPackage is considered no longer active
 * @property \Carbon\Carbon $effective_start The timestamp when the DuesPackage is considered newly active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $eligible_for_polo Whether this DuesPackage grants eligibility for a polo
 * @property bool $eligible_for_shirt Whether this DuesPackage grants eligibility for a shirt
 * @property bool $is_active Whether this DuesPackage is considered active
 * @property float $cost the cost of this package
 * @property int $available_for_purchase
 * @property int $id The database identifier for this DuesPackage
 * @property string $name
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $duesTransactions
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $transactions
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read bool $is_access_active
 * @property-read int|null $actions_count
 * @property-read int|null $dues_transactions_count
 * @property-read int|null $transactions_count
 */
class DuesPackage extends Model
{
    use Actionable;
    use HasFactory;
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


    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'effective_start' => 'datetime',
        'effective_end' => 'datetime',
        'access_start' => 'datetime',
        'access_end' => 'datetime',
        'cost' => 'float',
    ];

    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function duesTransactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
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
