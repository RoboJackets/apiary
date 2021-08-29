<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Laravel\Scout\Searchable;

/**
 * Maps together a Travel + User + Payment.
 *
 * @property int $id
 * @property int $user_id
 * @property int $travel_id
 * @property bool $documents_received
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read bool $is_paid
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Payment> $payment
 * @property-read int|null $payment_count
 * @property-read \App\Models\Travel $travel
 * @property-read \App\Models\User $user
 *
 * @method static Builder|TravelAssignment newModelQuery()
 * @method static Builder|TravelAssignment newQuery()
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment onlyTrashed()
 * @method static Builder|TravelAssignment paid()
 * @method static Builder|TravelAssignment query()
 * @method static Builder|TravelAssignment whereCreatedAt($value)
 * @method static Builder|TravelAssignment whereDeletedAt($value)
 * @method static Builder|TravelAssignment whereDocumentsReceived($value)
 * @method static Builder|TravelAssignment whereId($value)
 * @method static Builder|TravelAssignment whereTravelId($value)
 * @method static Builder|TravelAssignment whereUpdatedAt($value)
 * @method static Builder|TravelAssignment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class TravelAssignment extends Model
{
    use SoftDeletes;
    use GetMorphClassStatic;
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
        'documents_received' => 'boolean',
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
        'travel_name',
        'travel_destination',
        'travel_departure_date',
        'travel_return_date',
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
        'user_id',
        'travel_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function payment(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getIsPaidAttribute(): bool
    {
        return 0 !== self::where('travel_assignments.id', $this->id)->paid()->count();
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->select(
            'travel_assignments.*'
        )->leftJoin('payments', function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'travel_assignments.id')
                    ->where('payments.payable_type', '=', $this->getMorphClass())
                    ->where('payments.deleted_at', '=', null);
        })->join('travel', 'travel.id', '=', 'travel_assignments.travel_id')
            ->groupBy('travel_assignments.id', 'travel_assignments.travel_id', 'travel.fee_amount')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) >= travel.fee_amount');
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
        $travel = $this->travel->toArray();

        foreach ($user as $key => $val) {
            $array['user_'.$key] = $val;
        }

        foreach ($travel as $key => $val) {
            $array['travel_'.$key] = $val;
        }

        $array['payable_type'] = $this->getMorphClass();

        $array['updated_at_unix'] = $this->updated_at->getTimestamp();

        return $array;
    }
}
