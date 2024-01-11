<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $tar_received
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DocuSignEnvelope> $envelope
 * @property-read int|null $envelope_count
 * @property-read bool $is_complete
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Payment> $payment
 * @property-read bool $is_paid
 * @property-read int $payable_amount
 * @property-read bool $needs_docusign
 * @property-read int|null $payment_count
 * @property-read \App\Models\Travel|null $travel
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\TravelAssignmentFactory factory(...$parameters)
 * @method static Builder|TravelAssignment needDocuSign()
 * @method static Builder|TravelAssignment newModelQuery()
 * @method static Builder|TravelAssignment newQuery()
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment onlyTrashed()
 * @method static Builder|TravelAssignment paid()
 * @method static Builder|TravelAssignment query()
 * @method static Builder|TravelAssignment unpaid()
 * @method static Builder|TravelAssignment whereCreatedAt($value)
 * @method static Builder|TravelAssignment whereDeletedAt($value)
 * @method static Builder|TravelAssignment whereId($value)
 * @method static Builder|TravelAssignment whereTarReceived($value)
 * @method static Builder|TravelAssignment whereTravelId($value)
 * @method static Builder|TravelAssignment whereUpdatedAt($value)
 * @method static Builder|TravelAssignment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class TravelAssignment extends Model implements Payable
{
    use GetMorphClassStatic;
    use HasFactory;
    use Searchable;
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
        'tar_received' => 'boolean',
    ];

    /**
     * Get the User assigned to Travel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\TravelAssignment>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Travel assigned to User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Travel, \App\Models\TravelAssignment>
     */
    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    /**
     * Get the Payment for this assignment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Payment>
     */
    public function payment(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get the DocuSign envelope for this assignment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\DocuSignEnvelope>
     */
    public function envelope(): MorphMany
    {
        return $this->morphMany(DocuSignEnvelope::class, 'signable');
    }

    public function getIsPaidAttribute(): bool
    {
        return self::where('travel_assignments.id', $this->id)->paid()->count() !== 0;
    }

    public function getPayableAmountAttribute(): int
    {
        return $this->travel->fee_amount;
    }

    /**
     * Scope only paid assignments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\TravelAssignment>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\TravelAssignment>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->select('travel_assignments.*')->leftJoin('payments', function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'travel_assignments.id')
                ->where('payments.payable_type', '=', $this->getMorphClass())
                ->where('payments.deleted_at', '=', null);
        })->join('travel', 'travel.id', '=', 'travel_assignments.travel_id')
            ->groupBy('travel_assignments.id', 'travel_assignments.travel_id', 'travel.fee_amount')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) >= travel.fee_amount');
    }

    /**
     * Scope only unpaid assignments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\TravelAssignment>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\TravelAssignment>
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->select('travel_assignments.*')->leftJoin('payments', function (JoinClause $j): void {
            $j->on('payments.payable_id', '=', 'travel_assignments.id')
                ->where('payments.payable_type', '=', $this->getMorphClass())
                ->where('payments.deleted_at', '=', null);
        })->leftJoin('travel', 'travel.id', '=', 'travel_assignments.travel_id')
            ->groupBy('travel_assignments.id', 'travel_assignments.travel_id', 'travel.fee_amount')
            ->havingRaw('COALESCE(SUM(payments.amount),0.00) < travel.fee_amount');
    }

    /**
     * Scope only assignments that need a DocuSign packet.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\TravelAssignment>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\TravelAssignment>
     */
    public function scopeNeedDocuSign(Builder $query): Builder
    {
        return $query->whereHas('travel', static function (Builder $q): void {
            $q->where('tar_required', true);
        })
            ->where('tar_received', false);
    }

    public function getNeedsDocusignAttribute(): bool
    {
        return self::where('travel_assignments.id', $this->id)->needDocuSign()->count() !== 0;
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

        $array['updated_at_unix'] = $this->updated_at?->getTimestamp();

        return $array;
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->is_paid &&
            ($this->tar_received || ! $this->travel->tar_required) &&
            ($this->user->has_emergency_contact_information || $this->travel->return_date < Carbon::now());
    }
}
