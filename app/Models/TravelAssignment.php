<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingAfter

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
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $tar_received
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
 * @method static Builder|TravelAssignment whereId($value)
 * @method static Builder|TravelAssignment whereTravelId($value)
 * @method static Builder|TravelAssignment whereUpdatedAt($value)
 * @method static Builder|TravelAssignment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TravelAssignment withoutTrashed()
 * @method static Builder|TravelAssignment whereTarReceived($value)
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
        'user_revenue_total:desc',
        'user_attendance_count:desc',
        'user_signatures_count:desc',
        'user_recruiting_visits_count:desc',
        'user_gtid:desc',
        'updated_at_unix:desc',
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

    public function getTravelAuthorityRequestUrlAttribute(): string
    {
        return config('docusign.domestic_travel_authority_request.powerform_url').'&'.http_build_query(
            [
                config(
                    'docusign.domestic_travel_authority_request.fields.state_contract_airline'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'state_contract_airline' => false,
                ])['state_contract_airline'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request.fields.non_contract_airline'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'non_contract_airline' => false,
                ])['non_contract_airline'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request.fields.personal_automobile'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'personal_automobile' => false,
                ])['personal_automobile'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request.fields.rental_vehicle'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'rental_vehicle' => false,
                ])['rental_vehicle'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request.fields.other'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'other' => false,
                ])['other'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request.fields.itinerary'
                ) => $this->travel->tar_itinerary,

                config(
                    'docusign.domestic_travel_authority_request.fields.purpose'
                ) => $this->travel->tar_purpose,

                config(
                    'docusign.domestic_travel_authority_request.fields.airfare_cost'
                ) => $this->travel->tar_airfare,

                config(
                    'docusign.domestic_travel_authority_request.fields.other_cost'
                ) => $this->travel->tar_other_trans,

                config(
                    'docusign.domestic_travel_authority_request.fields.lodging_cost'
                ) => $this->travel->tar_lodging,

                config(
                    'docusign.domestic_travel_authority_request.fields.registration_cost'
                ) => $this->travel->tar_registration,

                config('docusign.domestic_travel_authority_request.fields.tar_total_cost') => (
                    ($this->travel->tar_airfare ?? 0) +
                    ($this->travel->tar_other_trans ?? 0) +
                    ($this->travel->tar_lodging ?? 0) +
                    ($this->travel->tar_registration ?? 0)
                ),

                config('docusign.domestic_travel_authority_request.fields.accounting_total_cost') => (
                    ($this->travel->tar_airfare ?? 0) +
                    ($this->travel->tar_other_trans ?? 0) +
                    ($this->travel->tar_lodging ?? 0) +
                    ($this->travel->tar_registration ?? 0)
                ),

                config(
                    'docusign.domestic_travel_authority_request.fields.peoplesoft_project_number'
                ) => $this->travel->tar_project_number,

                config(
                    'docusign.domestic_travel_authority_request.fields.peoplesoft_account_code'
                ) => $this->travel->tar_account_code,

                config(
                    'docusign.domestic_travel_authority_request.fields.departure_date'
                ) => $this->travel->departure_date->toDateString(),

                config(
                    'docusign.domestic_travel_authority_request.fields.return_date'
                ) => $this->travel->return_date->toDateString(),

                config(
                    'docusign.domestic_travel_authority_request.fields.covid_dates'
                ) => $this->travel->departure_date->toDateString().' - '
                    .$this->travel->return_date->toDateString(),

                config(
                    'docusign.domestic_travel_authority_request.fields.covid_destination'
                ) => $this->travel->destination,

                config(
                    'docusign.domestic_travel_authority_request.fields.home_department'
                ) => $this->user->home_department,

                config(
                    'docusign.domestic_travel_authority_request.traveler_name'
                ).'_UserName' => $this->user->full_name,

                config(
                    'docusign.domestic_travel_authority_request.traveler_name'
                ).'_Email' => $this->user->uid.'@gatech.edu',

                config(
                    'docusign.domestic_travel_authority_request.primary_contact_name'
                ).'_UserName' => $this->travel->primaryContact->full_name,

                config(
                    'docusign.domestic_travel_authority_request.primary_contact_name'
                ).'_Email' => $this->travel->primaryContact->uid.'@gatech.edu',
            ]
        );
    }
}
