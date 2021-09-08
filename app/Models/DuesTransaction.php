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
use Laravel\Scout\Searchable;

/**
 * Represents a completed or in progress dues payment.
 *
 * @property int $id
 * @property int $dues_package_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\DuesPackage $for
 * @property-read bool $is_paid
 * @property-read string $status
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Merchandise> $merchandise
 * @property-read int|null $merchandise_count
 * @property-read \App\Models\DuesPackage $package
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Payment> $payment
 * @property-read int|null $payment_count
 * @property-read \App\Models\User $user
 * @method static Builder|DuesTransaction accessCurrent()
 * @method static Builder|DuesTransaction current()
 * @method static \Database\Factories\DuesTransactionFactory factory(...$parameters)
 * @method static Builder|DuesTransaction newModelQuery()
 * @method static Builder|DuesTransaction newQuery()
 * @method static \Illuminate\Database\Query\Builder|DuesTransaction onlyTrashed()
 * @method static Builder|DuesTransaction paid()
 * @method static Builder|DuesTransaction pending()
 * @method static Builder|DuesTransaction query()
 * @method static Builder|DuesTransaction unpaid()
 * @method static Builder|DuesTransaction whereCreatedAt($value)
 * @method static Builder|DuesTransaction whereDeletedAt($value)
 * @method static Builder|DuesTransaction whereDuesPackageId($value)
 * @method static Builder|DuesTransaction whereId($value)
 * @method static Builder|DuesTransaction whereUpdatedAt($value)
 * @method static Builder|DuesTransaction whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|DuesTransaction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DuesTransaction withoutTrashed()
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
 */
class DuesTransaction extends Model
{
    use GetMorphClassStatic;
    use HasFactory;
    use SoftDeletes;
    use Searchable;

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
     * The attributes that should be searchable in Meilisearch.
     *
     * @var array<string>
     */
    public $searchable_attributes = [
        'user_first_name',
        'user_preferred_name',
        'user_last_name',
        'user_uid',
        'user_gt_email',
        'user_personal_email',
        'user_gmail_address',
        'user_clickup_email',
        'user_autodesk_email',
        'user_github_username',
        'package_name',
        'package_effective_start',
        'package_effective_end',
        'status',
        'payable_type',
    ];

    /**
     * The rules to use for ranking results in Meilisearch.
     *
     * @var array<string>
     */
    public $ranking_rules = [
        'desc(user_revenue_total)',
        'desc(user_attendance_count)',
        'desc(user_signatures_count)',
        'desc(user_recruiting_visits_count)',
        'desc(user_gtid)',
        'desc(updated_at_unix)',
    ];

    /**
     * The attributes that can be used for filtering in Meilisearch.
     *
     * @var array<string>
     */
    public $filterable_attributes = [
        'dues_package_id',
        'user_id',
        'merchandise_id',
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

    public function jankForNova(): BelongsToMany
    {
        return $this->merchandise()->as('jankForNova');
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

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $user = $this->user->toSearchableArray();
        $package = $this->package->toArray();

        foreach ($user as $key => $val) {
            $array['user_'.$key] = $val;
        }

        foreach ($package as $key => $val) {
            $array['package_'.$key] = $val;
        }

        $array['payable_type'] = $this->getMorphClass();

        $array['dues_package_id'] = $this->package->id;

        $array['user_id'] = $this->user->id;

        $array['updated_at_unix'] = $this->updated_at->getTimestamp();

        $array['merchandise_id'] = $this->merchandise->modelKeys();

        return $array;
    }

    /**
     * Magic for making relationships work on pivot models in Nova. Do not use for anything else.
     */
    public function providedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provided_by');
    }

    /**
     * Magic for making relationships work on pivot models in Nova. Do not use for anything else.
     */
    public function getJankForNovaAttribute(): DuesTransactionMerchandise
    {
        $viaResource = request()->viaResource;
        $viaResourceId = request()->viaResourceId;

        if ('merchandise' === $viaResource && null !== $viaResourceId) {
            return DuesTransactionMerchandise::where('dues_transaction_id', $this->id)
                ->where('merchandise_id', $viaResourceId)
                ->sole();
        }

        return new DuesTransactionMerchandise();
    }
}
