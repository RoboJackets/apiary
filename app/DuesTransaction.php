<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

/**
 * Represents a completed or in progress dues payment.
 *
 * @method static \Illuminate\Database\Eloquent\Builder current() Scopes a query to only current transactions
 * @method static \Illuminate\Database\Eloquent\Builder paid() Scopes a query to only paid transactions
 * @method static \Illuminate\Database\Eloquent\Builder pending() Scopes a query to only pending transactions
 * @method static \Illuminate\Database\Eloquent\Builder pendingSwag() Scopes a query to only transactions that need
 * @method static Builder|DuesTransaction accessCurrent()
 * @method static Builder|DuesTransaction newModelQuery()
 * @method static Builder|DuesTransaction newQuery()
 * @method static Builder|DuesTransaction query()
 * @method static Builder|DuesTransaction unpaid()
 * @method static Builder|DuesTransaction whereCreatedAt($value)
 * @method static Builder|DuesTransaction whereDeletedAt($value)
 * @method static Builder|DuesTransaction whereDuesPackageId($value)
 * @method static Builder|DuesTransaction whereId($value)
 * @method static Builder|DuesTransaction wherePaymentId($value)
 * @method static Builder|DuesTransaction whereReceivedPolo($value)
 * @method static Builder|DuesTransaction whereReceivedShirt($value)
 * @method static Builder|DuesTransaction whereSwagPoloProvided($value)
 * @method static Builder|DuesTransaction whereSwagPoloProvidedBy($value)
 * @method static Builder|DuesTransaction whereSwagShirtProvided($value)
 * @method static Builder|DuesTransaction whereSwagShirtProvidedBy($value)
 * @method static Builder|DuesTransaction whereUpdatedAt($value)
 * @method static Builder|DuesTransaction whereUserId($value)
 * @method static QueryBuilder|DuesTransaction onlyTrashed()
 * @method static QueryBuilder|DuesTransaction withoutTrashed()
 * @method static QueryBuilder|DuesTransaction withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property ?int $swag_polo_providedBy the user ID that distributed a polo for this transaction
 * @property ?int $swag_shirt_providedBy the user ID that distributed a shirt for this transaction
 * @property ?string $swag_polo_provided The timestamp of when a polo was given for this DuesTransaction, or null
 * @property ?string $swag_shirt_provided The timestamp of when a shirt was given for this DuesTransaction, or null
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $is_paid whether this transaction is paid in full
 * @property int $dues_package_id
 * @property int $id The database ID for this DuesTransaction
 * @property int $received_polo
 * @property int $received_shirt
 * @property int $user_id
 * @property int|null $payment_id
 * @property string $status the status of this transaction
 *
 * @property-read \App\DuesPackage $for
 * @property-read \App\DuesPackage $package
 * @property-read \App\User $swagPoloProvidedBy
 * @property-read \App\User $swagShirtProvidedBy
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Payment> $payment
 * @property-read int|null $payment_count
 * @property-read string $swag_polo_status
 * @property-read string $swag_shirt_status
 */
class DuesTransaction extends Model
{
    use SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['status', 'swag_polo_status', 'swag_shirt_status'];

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
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'swag_shirt_provided',
        'swag_polo_provided',
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
     * Get the User associated with the swag_shirt_providedBy field on the DuesTransaction model.
     */
    public function swagShirtProvidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swag_shirt_providedBy', 'id');
    }

    /**
     * Get the User associated with the swag_polo_providedBy field on the DuesTransaction model.
     */
    public function swagPoloProvidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swag_polo_providedBy', 'id');
    }

    /**
     * Alias the generalize form of the Transaction for Polymorphic Reasons.
     */
    public function for(): BelongsTo
    {
        return $this->package();
    }

    /**
     * Get the status flag for the Transaction.
     */
    public function getStatusAttribute(): string
    {
        if (null === $this->package || ! $this->package->is_active) {
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
     * Get the swag polo status attribute for the Transaction.
     */
    public function getSwagPoloStatusAttribute(): string
    {
        if (null === $this->package || $this->package->eligible_for_polo && null === $this->swag_polo_provided) {
            return 'Not Picked Up';
        }

        if ($this->package->eligible_for_polo && null !== $this->swag_polo_provided) {
            return 'Picked Up';
        }

        return 'Not Eligible';
    }

    /**
     * Get the swag shirt status attribute for the Transaction.
     */
    public function getSwagShirtStatusAttribute(): string
    {
        if (null === $this->package || $this->package->eligible_for_shirt && null === $this->swag_shirt_provided) {
            return 'Not Picked Up';
        }

        if ($this->package->eligible_for_shirt && null !== $this->swag_shirt_provided) {
            return 'Picked Up';
        }

        return 'Not Eligible';
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
     * Scope a query to only include swag-pending transactions.
     * Swag-pending defined as a paid transaction that has not provided shirt/polo
     * Note that you can't just chain the paid() scope to this because it breaks the joins.
     */
    public function scopePendingSwag(Builder $query): Builder
    {
        return $query->select(
            'dues_transactions.*',
            'dues_packages.eligible_for_shirt',
            'dues_packages.eligible_for_polo'
        )->join('dues_packages', static function (JoinClause $j): void {
            $j->on('dues_packages.id', '=', 'dues_transactions.dues_package_id')
                ->where(static function (QueryBuilder $q): void {
                    $q->where('dues_packages.eligible_for_shirt', '=', true)
                        ->where('dues_transactions.swag_shirt_provided', '=', null)
                        ->orWhere(static function (QueryBuilder $q): void {
                            $q->where('dues_packages.eligible_for_polo', '=', true)
                                ->where('dues_transactions.swag_polo_provided', '=', null);
                        });
                });
        })->leftJoin('payments', static function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'dues_transactions.id')
                ->where('payments.payable_type', '=', self::class)
                ->where('payments.deleted_at', '=', null);
        })->groupBy('dues_transactions.id', 'dues_transactions.dues_package_id', 'dues_packages.cost')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) >= dues_packages.cost');
    }

    /**
     * Scope a query to only include paid transactions
     * Paid defined as one or more payments whose total is equal to the payable amount.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->select(
            'dues_transactions.*'
        )->leftJoin('payments', static function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', self::class)
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
        )->leftJoin('payments', static function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'dues_transactions.id')
                    ->where('payments.payable_type', '=', self::class)
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
