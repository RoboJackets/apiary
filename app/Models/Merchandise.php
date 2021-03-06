<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents one option of merch/swag related to a fiscal year and its dues packages.
 *
 * @property      int $id
 * @property      string $name
 * @property      int $fiscal_year_id
 * @property      \Illuminate\Support\Carbon|null $created_at
 * @property      \Illuminate\Support\Carbon|null $updated_at
 * @property      \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\FiscalYear $fiscalYear
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesPackage> $packages
 * @property-read int|null $packages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise newModelQuery()
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise newQuery()
 * @method        static \Illuminate\Database\Query\Builder|Merchandise onlyTrashed()
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise query()
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise whereCreatedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise whereDeletedAt($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise whereFiscalYearId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise whereId($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise whereName($value)
 * @method        static \Illuminate\Database\Eloquent\Builder|Merchandise whereUpdatedAt($value)
 * @method        static \Illuminate\Database\Query\Builder|Merchandise withTrashed()
 * @method        static \Illuminate\Database\Query\Builder|Merchandise withoutTrashed()
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Merchandise extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'merchandise';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'fiscal_year_id',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(DuesPackage::class)->withPivot('group')->withTimestamps();
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(DuesTransaction::class)
            ->withPivot(['provided_at', 'provided_by'])
            ->withTimestamps()
            ->using(DuesTransactionMerchandise::class);
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'packages' => 'dues-packages',
            'transactions' => 'dues-transactions',
        ];
    }
}
