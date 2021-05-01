<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;

/**
 * Represents a completed or in progress dues payment.
 *
 * @method static \Illuminate\Database\Eloquent\Builder current() Scopes a query to only current transactions
 * @method static \Illuminate\Database\Eloquent\Builder paid() Scopes a query to only paid transactions
 * @method static \Illuminate\Database\Eloquent\Builder pending() Scopes a query to only pending transactions
 * @method static Builder|DuesTransaction accessCurrent()
 * @method static Builder|DuesTransaction newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static Builder|DuesTransaction query()
 * @method static Builder|DuesTransaction unpaid()
 * @method static Builder|DuesTransaction whereCreatedAt($value)
 * @method static Builder|DuesTransaction whereDeletedAt($value)
 * @method static Builder|DuesTransaction whereDuesPackageId($value)
 * @method static Builder|DuesTransaction whereId($value)
 * @method static Builder|DuesTransaction wherePaymentId($value)
 * @method static Builder|DuesTransaction whereUpdatedAt($value)
 * @method static Builder|DuesTransaction whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|DuesTransaction onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|DuesTransaction withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|DuesTransaction withTrashed()
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property ?\Carbon\Carbon $deleted_at
 * @property bool $is_paid whether this transaction is paid in full
 * @property int $dues_package_id
 * @property int $id The database ID for this DuesTransaction
 * @property int $user_id
 * @property int|null $payment_id
 * @property string $status the status of this transaction
 * @property-read \App\Models\DuesPackage $for
 * @property-read \App\Models\DuesPackage $package
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection $payment
 * @property-read int|null $payment_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Merchandise[] $merchandise
 * @property-read int|null $merchandise_count
 * @method static \Database\Factories\DuesTransactionFactory factory(...$parameters)
 */
class DuesTransaction extends Model
{
    use GetMorphClassStatic;
    use HasFactory;
    use SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['status'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'status',
    ];

    /**
     * Get the Payment associated with the DuesTransaction model.
     */
    public function payment(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get the DuesPackage associated with the DuesTransaction model.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(DuesPackage::class, 'dues_package_id');
    }

    /**
     * Get the User associated with the DuesTransaction model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias the generalize form of the Transaction for Polymorphic Reasons.
     */
    public function for(): BelongsTo
    {
        return $this->package();
    }

    public function merchandise(): BelongsToMany
    {
        return $this->belongsToMany(Merchandise::class)
            ->withPivot(['provided_at', 'provided_by'])
            ->withTimestamps()
            ->using(DuesTransactionMerchandise::class);
    }

    /**
     * Get the status flag for the Transaction.
     */
    public function getStatusAttribute(): string
    {
        if (! $this->package->is_active) {
            return 'expired';
        }

        if (0 === $this->payment->count()
            || floatval($this->payment->sum('amount')) < floatval($this->getPayableAmount())
        ) {
            return 'pending';
        }

        return 'paid';
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'user' => 'users',
            'package' => 'dues-packages',
            'payment' => 'payments',
            'user.teams' => 'teams-membership',
            'merchandise' => 'merchandise',
        ];
    }

    /**
     * Scope a query to only include pending transactions.
     * Pending defined as no payments, or payments that do not sum to payable amount
     * for a currently active DuesPackage.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->current()->unpaid();
    }

    /**
     * Scope a query to only include paid transactions
     * Paid defined as one or more payments whose total is equal to the payable amount.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->select(
            'dues_transactions.*'
        )->leftJoin('payments', function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', $this->getMorphClass())
                    ->where('payments.deleted_at', '=', null);
        })->join('dues_packages', 'dues_packages.id', '=', 'dues_transactions.dues_package_id')
            ->groupBy('dues_transactions.id', 'dues_transactions.dues_package_id', 'dues_packages.cost')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) >= dues_packages.cost');
    }

    /**
     * Get the is_paid flag for the DuesTransaction.
     */
    public function getIsPaidAttribute(): bool
    {
        return 0 !== self::where('dues_transactions.id', $this->id)->paid()->count();
    }

    /**
     * Scope a query to only include unpaid transactions
     * Unpaid defined as zero or more payments that are less than the payable amount.
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->select(
            'dues_transactions.*'
        )->leftJoin('payments', function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', $this->getMorphClass())
                    ->where('payments.deleted_at', '=', null);
        })->join('dues_packages', 'dues_packages.id', '=', 'dues_transactions.dues_package_id')
            ->groupBy('dues_transactions.id', 'dues_transactions.dues_package_id', 'dues_packages.cost')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) < dues_packages.cost');
    }

    /**
     * Scope a query to only include current transactions.
     * Current defined as belonging to an active DuesPackage.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereHas('package', static function (Builder $q): void {
            $q->active();
        });
    }

    /**
     * Scope a query to only include current transactions.
     * Current defined as belonging to an active DuesPackage.
     */
    public function scopeAccessCurrent(Builder $query): Builder
    {
        return $query->whereHas('package', static function (Builder $q): void {
            $q->accessActive();
        });
    }

    /**
     * Get the Payable amount.
     */
    public function getPayableAmount(): float
    {
        return $this->package->cost;
    }
}
