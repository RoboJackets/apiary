<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\DuesTransactionMerchandise.
 *
 * @property      int $id
 * @property      int $dues_transaction_id
 * @property      int $merchandise_id
 * @property      \Illuminate\Support\Carbon|null $provided_at
 * @property      int|null $provided_by
 * @property      \Illuminate\Support\Carbon|null $created_at
 * @property      \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $provided_by_name
 * @property-read \App\Models\User|null $providedBy
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise newModelQuery()
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise newQuery()
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise query()
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereCreatedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereDuesTransactionId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereMerchandiseId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereProvidedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereProvidedBy($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|DuesTransactionMerchandise whereUpdatedAt($value)
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
 */
class DuesTransactionMerchandise extends Pivot
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $casts = [
        'provided_at' => 'datetime',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    public function providedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provided_by');
    }

    public function ComputedField(): BelongsTo
    {
        return $this->providedBy();
    }
}
