<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\FiscalYear.
 *
 * @property int $id
 * @property string $ending_year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\Merchandise> $merchandise
 * @property-read int|null $merchandise_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\DuesPackage> $packages
 * @property-read int|null $packages_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear query()
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear whereEndingYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FiscalYear whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class FiscalYear extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the DuesPackages that are in this FiscalYear.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesPackage, self>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(DuesPackage::class);
    }

    /**
     * Get the Merchandise for this FiscalYear.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Merchandise, self>
     */
    public function merchandise(): HasMany
    {
        return $this->hasMany(Merchandise::class);
    }
}
