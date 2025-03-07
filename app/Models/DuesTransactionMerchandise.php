<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

/**
 * App\Models\DuesTransactionMerchandise.
 *
 * @property int $id
 * @property int $dues_transaction_id
 * @property int $merchandise_id
 * @property \Illuminate\Support\Carbon|null $provided_at
 * @property int|null $provided_by
 * @property ?string $provided_via
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $providedBy
 * @property-read ?string $size
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise query()
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereDuesTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereMerchandiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereProvidedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereProvidedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class DuesTransactionMerchandise extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provided_at' => 'datetime',
        ];
    }

    /**
     * Magic for making relationships work on pivot models in Nova. Do not use for anything else.
     *
     * @return BelongsTo<User, DuesTransactionMerchandise>
     */
    public function providedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provided_by');
    }

    /**
     * Relationship for dues transaction.
     *
     * @return BelongsTo<DuesTransaction, DuesTransactionMerchandise>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(DuesTransaction::class, 'dues_transaction_id');
    }

    /**
     * Relationship for merchandise.
     *
     * @return BelongsTo<Merchandise, DuesTransactionMerchandise>
     */
    public function merchandise(): BelongsTo
    {
        return $this->belongsTo(Merchandise::class);
    }

    /**
     * Get the size for this DuesTransactionMerchandise based on data about the merchandise item and user.
     */
    public function getSizeAttribute(): ?string
    {
        if (Str::contains(Str::lower($this->merchandise->name), 'shirt')) {
            return $this->transaction->user->shirt_size;
        } elseif (Str::contains(Str::lower($this->merchandise->name), 'polo')) {
            return $this->transaction->user->polo_size;
        } else {
            return null;
        }
    }
}
