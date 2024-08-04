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
 * @property int $id
 * @property string $name
 * @property int $fiscal_year_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $distributable
 * @property-read \App\Models\FiscalYear $fiscalYear
 * @property-read \App\Models\DuesTransactionMerchandise $jank_for_nova
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $jankForNova
 * @property-read int|null $jank_for_nova_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesPackage> $packages
 * @property-read int|null $packages_count
 * @property-read \App\Models\User $providedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise newQuery()
 * @method static \Illuminate\Database\Query\Builder|Merchandise onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise query()
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereDistributable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereFiscalYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Merchandise whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Merchandise withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Merchandise withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'distributable' => 'boolean',
        ];
    }

    public const RELATIONSHIP_PERMISSIONS = [
        'packages' => 'read-dues-packages',
        'transactions' => 'read-dues-transactions',
    ];

    /**
     * Get the fiscal year for this merchandise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\FiscalYear, \App\Models\Merchandise>
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get the associated packages for this merchandise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\DuesPackage>
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(DuesPackage::class)->withPivot('group')->withTimestamps();
    }

    /**
     * Get the associated transactions for this merchandise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\DuesTransaction>
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(DuesTransaction::class)
            ->withPivot(['provided_at', 'provided_by'])
            ->withTimestamps()
            ->using(DuesTransactionMerchandise::class);
    }

    /**
     * Get the associated transactions for this merchandise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\DuesTransaction>
     */
    public function jankForNova(): BelongsToMany
    {
        return $this->transactions()->as('jankForNova');
    }

    /**
     * Magic for making relationships work on pivot models in Nova. Do not use for anything else.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Merchandise>
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

        if ($viaResource === 'dues-transactions' && $viaResourceId !== null) {
            return DuesTransactionMerchandise::where('dues_transaction_id', $viaResourceId)
                ->where('merchandise_id', $this->id)
                ->sole();
        }

        return new DuesTransactionMerchandise();
    }
}
