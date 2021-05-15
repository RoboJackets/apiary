<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;

/**
 * Maps together a Travel + User + Payment
 *
 * @property int $id
 * @property int $user_id
 * @property int $travel_id
 * @property bool $documents_received
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @mixin \Eloquent
 */
class TravelAssignment extends Model
{
    use SoftDeletes;

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
}
