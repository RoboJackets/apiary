<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
        return $this->morphMany(\App\Payment::class, 'payable');
    }

    /**
     * Get the DuesPackage associated with the DuesTransaction model.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(\App\DuesPackage::class, 'dues_package_id');
    }

    /**
     * Get the User associated with the DuesTransaction model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Get the User associated with the swag_shirt_providedBy field on the DuesTransaction model.
     */
    public function swagShirtProvidedBy(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'swag_shirt_providedBy', 'id');
    }

    /**
     * Get the User associated with the swag_polo_providedBy field on the DuesTransaction model.
     */
    public function swagPoloProvidedBy(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'swag_polo_providedBy', 'id');
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
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return string
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
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->current()->unpaid();
    }

    /**
     * Scope a query to only include swag-pending transactions.
     * Swag-pending defined as a paid transaction that has not provided shirt/polo
     * Note that you can't just chain the paid() scope to this because it breaks the joins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
     *
     * @return bool
     */
    public function getIsPaidAttribute(): bool
    {
        return 0 !== self::where('dues_transactions.id', $this->id)->paid()->get()->count();
    }

    /**
     * Scope a query to only include unpaid transactions
     * Unpaid defined as zero or more payments that are less than the payable amount.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
    public function getPayableAmount(): string
    {
        return $this->package->cost;
    }
}
