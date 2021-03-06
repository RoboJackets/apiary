<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * Represents a single trip.
 *
 * @property int $id
 * @property string $name
 * @property string $destination
 * @property int $primary_contact_user_id
 * @property \Illuminate\Support\Carbon $departure_date
 * @property \Illuminate\Support\Carbon $return_date
 * @property int $fee_amount
 * @property string $included_with_fee
 * @property string|null $not_included_with_fee
 * @property string|null $documents_required
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\TravelAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \App\Models\User $primaryContact
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Travel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Travel newQuery()
 * @method static \Illuminate\Database\Query\Builder|Travel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Travel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereDepartureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereDocumentsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereFeeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereIncludedWithFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereNotIncludedWithFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel wherePrimaryContactUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Travel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Travel withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Travel extends Model
{
    use SoftDeletes;
    use Searchable;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'created_at',
        'deleted_at',
        'id',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * The rules to use for ranking results in Meilisearch.
     *
     * @var array<string>
     */
    public $ranking_rules = [
        'desc(departure_date_unix)',
        'desc(return_date_unix)',
    ];

    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_contact_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TravelAssignment::class);
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('primaryContact');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        if (! array_key_exists('primary_contact', $array)) {
            $array['primary_contact'] = $this->primaryContact->toArray();
        }

        $array['departure_date_unix'] = $this->departure_date->getTimestamp();

        $array['return_date_unix'] = $this->return_date->getTimestamp();

        return $array;
    }
}
