<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
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
 * @property bool $available_for_purchase
 * @property bool $restricted_to_students
 * @property int $id The database identifier for this DuesPackage
 * @property int $conflicts_with_dues_package_id The dues package that would restrict purchase of this package
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
        'available_for_purchase' => 'boolean',
        'restricted_to_students' => 'boolean',
    ];

    /**
     * Get the DuesTransactions associated with the DuesPackage model.
     */
    public function duesTransactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the DuesTransactions associated with the DuesPackage model.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the FiscalYear associated with the DuesPackage model.
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function conflictsWith(): BelongsTo
    {
        return $this->belongsTo(self::class, 'conflicts_with_package_id');
    }

    public function hasConflictWith(): HasMany
    {
        return $this->hasMany(self::class, 'conflicts_with_package_id');
    }

    /**
     * Scope a query to only include DuesPackages available for purchase.
     */
    public function scopeAvailableForPurchase(Builder $query): Builder
    {
        return $query->where('available_for_purchase', true);
    }

    public function scopeUserCanPurchase(Builder $query, User $user): Builder
    {
        return $query
            ->select('dues_packages.*')
            ->leftJoin('dues_transactions', static function (JoinClause $join) use ($user): void {
                $join->on('dues_packages.conflicts_with_package_id', '=', 'dues_transactions.dues_package_id')
                     ->where('dues_transactions.user_id', $user->id);
            })
            ->leftJoin('payments', static function (JoinClause $join): void {
                $join->on('payments.payable_id', '=', 'dues_transactions.id')
                        ->where('payments.payable_type', '=', DuesTransaction::getMorphClassStatic())
                        ->where('payments.deleted_at', '=', null)
                        ->where('payments.amount', '>', 0);
            })
            ->where('available_for_purchase', true)
            ->where('dues_packages.effective_end', '>=', date('Y-m-d'))
            ->where('restricted_to_students', 'student' === $user->primary_affiliation)
            ->where('payments.id', null);
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
