<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\DuesPackageObserver;
use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a possible dues payment and what privileges are associated with it.
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $effective_start
 * @property \Illuminate\Support\Carbon $effective_end
 * @property \Illuminate\Support\Carbon|null $access_start
 * @property \Illuminate\Support\Carbon|null $access_end
 * @property float $cost
 * @property bool $available_for_purchase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $fiscal_year_id
 * @property int|null $conflicts_with_package_id
 * @property bool|null $restricted_to_students
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read DuesPackage|null $conflictsWith
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $duesTransactions
 * @property-read int|null $dues_transactions_count
 * @property-read \App\Models\FiscalYear|null $fiscalYear
 * @property-read bool $is_access_active
 * @property-read bool $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection|array<DuesPackage> $hasConflictWith
 * @property-read int|null $has_conflict_with_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Merchandise> $merchandise
 * @property-read int|null $merchandise_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static Builder|DuesPackage accessActive()
 * @method static Builder|DuesPackage active()
 * @method static Builder|DuesPackage availableForPurchase()
 * @method static \Database\Factories\DuesPackageFactory factory(...$parameters)
 * @method static Builder|DuesPackage newModelQuery()
 * @method static Builder|DuesPackage newQuery()
 * @method static \Illuminate\Database\Query\Builder|DuesPackage onlyTrashed()
 * @method static Builder|DuesPackage query()
 * @method static Builder|DuesPackage userCanPurchase(\App\Models\User $user)
 * @method static Builder|DuesPackage whereAccessEnd($value)
 * @method static Builder|DuesPackage whereAccessStart($value)
 * @method static Builder|DuesPackage whereAvailableForPurchase($value)
 * @method static Builder|DuesPackage whereConflictsWithPackageId($value)
 * @method static Builder|DuesPackage whereCost($value)
 * @method static Builder|DuesPackage whereCreatedAt($value)
 * @method static Builder|DuesPackage whereDeletedAt($value)
 * @method static Builder|DuesPackage whereEffectiveEnd($value)
 * @method static Builder|DuesPackage whereEffectiveStart($value)
 * @method static Builder|DuesPackage whereFiscalYearId($value)
 * @method static Builder|DuesPackage whereId($value)
 * @method static Builder|DuesPackage whereName($value)
 * @method static Builder|DuesPackage whereRestrictedToStudents($value)
 * @method static Builder|DuesPackage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|DuesPackage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DuesPackage withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
 */
#[ObservedBy([DuesPackageObserver::class])]
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_start' => 'datetime',
            'effective_end' => 'datetime',
            'access_start' => 'datetime',
            'access_end' => 'datetime',
            'cost' => 'float',
            'available_for_purchase' => 'boolean',
            'restricted_to_students' => 'boolean',
        ];
    }

    public const RELATIONSHIP_PERMISSIONS = [
        'transactions' => 'read-dues-transactions',
        'merchandise' => 'read-merchandise',
    ];

    /**
     * Get the DuesTransactions associated with the DuesPackage model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesTransaction, self>
     */
    public function duesTransactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the DuesTransactions associated with the DuesPackage model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesTransaction, self>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the FiscalYear associated with the DuesPackage model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\FiscalYear, \App\Models\DuesPackage>
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get the conflicts for this dues package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\DuesPackage, \App\Models\DuesPackage>
     */
    public function conflictsWith(): BelongsTo
    {
        return $this->belongsTo(self::class, 'conflicts_with_package_id');
    }

    /**
     * Get the conflicts for this dues package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesPackage, self>
     */
    public function hasConflictWith(): HasMany
    {
        return $this->hasMany(self::class, 'conflicts_with_package_id');
    }

    /**
     * Get the available merchandise for this dues package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Merchandise, self>
     */
    public function merchandise(): BelongsToMany
    {
        return $this->belongsToMany(Merchandise::class)->withPivot('group')->withTimestamps();
    }

    /**
     * Scope a query to only include DuesPackages available for purchase.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>
     */
    public function scopeAvailableForPurchase(Builder $query): Builder
    {
        return $query->where('available_for_purchase', true);
    }

    /**
     * Scope a query to only include DuesPackages that a specific user can purchase. Excludes conflicts and
     * restricted packages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>
     */
    public function scopeUserCanPurchase(Builder $query, User $user): Builder
    {
        $newQuery = $query
            ->selectRaw('dues_packages.*, payments_ucp.id as payments_id')
            ->leftJoin(
                'dues_transactions as dues_transactions_ucp',
                static function (JoinClause $join) use ($user): void {
                    $join->on('dues_packages.conflicts_with_package_id', '=', 'dues_transactions_ucp.dues_package_id')
                        ->where('dues_transactions_ucp.user_id', $user->id)
                        ->whereNull('dues_transactions_ucp.deleted_at');
                }
            )
            ->leftJoin('payments as payments_ucp', static function (JoinClause $join): void {
                $join->on('payments_ucp.payable_id', '=', 'dues_transactions_ucp.id')
                    ->where('payments_ucp.payable_type', '=', DuesTransaction::getMorphClassStatic())
                    ->where('payments_ucp.deleted_at', '=', null)
                    ->where('payments_ucp.amount', '>', 0);
            })
            ->where('available_for_purchase', true)
            ->where('dues_packages.effective_end', '>=', now())
            ->where('restricted_to_students', $user->is_student);

        if (config('database.default') === 'mysql') {
            $newQuery->havingRaw('payments_ucp.id is null');
        }

        return $newQuery;
    }

    /**
     * Scope a query to only include active DuesPackages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('effective_start', '<', now())
            ->where('effective_end', '>', now());
    }

    /**
     * Scope a query to only include access active DuesPackages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\DuesPackage>
     */
    public function scopeAccessActive(Builder $query, ?CarbonImmutable $asOfTimestamp = null): Builder
    {
        return $query->where('access_start', '<', $asOfTimestamp ?? CarbonImmutable::now())
            ->where('access_end', '>', $asOfTimestamp ?? CarbonImmutable::now());
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
}
