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
 * @property-read string $travel_authority_request_url
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

    public function getTravelAuthorityRequestUrlAttribute(): string
    {
        if (
            ($this->travel->tar_transportation_mode ?? [
                'state_contract_airline' => false,
            ])['state_contract_airline'] === true ||
            true === ($this->travel->tar_transportation_mode ?? [
                'non_contract_airline' => false,
            ])['non_contract_airline']
        ) {
            if ($this->travel->is_international) {
                return $this->getInternationalAirfarePowerFormUrl();
            }

            return $this->getDomesticAirfarePowerFormUrl();
        }

        if (true === ($this->travel->tar_transportation_mode ?? ['rental_vehicle' => false])['rental_vehicle']) {
            return $this->getTravelAuthorityRequestPowerFormUrl();
        }

        return $this->getCovidPowerFormUrl();
    }

    private function getDomesticAirfarePowerFormUrl(): string
    {
        return config('docusign.domestic_travel_authority_request_with_airfare.powerform_url').'&'.http_build_query(
            [
                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.state_contract_airline'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'state_contract_airline' => false,
                ])['state_contract_airline'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.non_contract_airline'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'non_contract_airline' => false,
                ])['non_contract_airline'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.personal_automobile'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'personal_automobile' => false,
                ])['personal_automobile'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.rental_vehicle'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'rental_vehicle' => false,
                ])['rental_vehicle'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.other'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'other' => false,
                ])['other'] ? 'x' : '',

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.itinerary'
                ) => $this->travel->tar_itinerary,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.purpose'
                ) => $this->travel->tar_purpose,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.airfare_cost'
                ) => $this->travel->tar_airfare,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.other_cost'
                ) => $this->travel->tar_other_trans,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.lodging_cost'
                ) => $this->travel->tar_lodging,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.registration_cost'
                ) => $this->travel->tar_registration,

                config('docusign.domestic_travel_authority_request_with_airfare.fields.tar_total_cost') => (
                    ($this->travel->tar_airfare ?? 0) +
                    ($this->travel->tar_other_trans ?? 0) +
                    ($this->travel->tar_lodging ?? 0) +
                    ($this->travel->tar_registration ?? 0)
                ),

                config('docusign.domestic_travel_authority_request_with_airfare.fields.accounting_total_cost') => (
                    ($this->travel->tar_airfare ?? 0) +
                    ($this->travel->tar_other_trans ?? 0) +
                    ($this->travel->tar_lodging ?? 0) +
                    ($this->travel->tar_registration ?? 0)
                ),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.peoplesoft_project_number'
                ) => $this->travel->tar_project_number,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.peoplesoft_account_code'
                ) => $this->travel->tar_account_code,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.departure_date'
                ) => $this->travel->departure_date->toDateString(),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.return_date'
                ) => $this->travel->return_date->toDateString(),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.covid_dates'
                ) => $this->travel->departure_date->toDateString().' - '
                    .$this->travel->return_date->toDateString(),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.covid_destination'
                ) => $this->travel->destination,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.home_department'
                ) => $this->user->employee_home_department,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.employee_id'
                ) => $this->user->employee_id,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.airfare_phone'
                ) => $this->user->phone,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.airfare_non_employee_checkbox'
                ) => ($this->user->employee_home_department === null ? 'x' : ''),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.airfare_employee_checkbox'
                ) => ($this->user->employee_home_department === null ? '' : 'x'),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.airfare_non_employee_domestic_checkbox'
                ) => ($this->user->employee_home_department === null ? 'x' : ''),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.fields.airfare_employee_domestic_checkbox'
                ) => ($this->user->employee_home_department === null ? '' : 'x'),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.traveler_name'
                ).'_UserName' => $this->user->full_name,

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.traveler_name'
                ).'_Email' => $this->user->uid.'@gatech.edu',

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.ingest_mailbox_name'
                ).'_UserName' => config('app.name'),

                config(
                    'docusign.domestic_travel_authority_request_with_airfare.ingest_mailbox_name'
                ).'_Email' => config('docusign.ingest_mailbox'),
            ]
        );
    }

    private function getInternationalAirfarePowerFormUrl(): string
    {
        return config(
            'docusign.international_travel_authority_request_with_airfare.powerform_url'
        ).'&'.http_build_query(
            [
                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.state_contract_airline'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'state_contract_airline' => false,
                ])['state_contract_airline'] ? 'x' : '',

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.non_contract_airline'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'non_contract_airline' => false,
                ])['non_contract_airline'] ? 'x' : '',

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.personal_automobile'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'personal_automobile' => false,
                ])['personal_automobile'] ? 'x' : '',

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.rental_vehicle'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'rental_vehicle' => false,
                ])['rental_vehicle'] ? 'x' : '',

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.other'
                ) => ($this->travel->tar_transportation_mode ?? [
                    'other' => false,
                ])['other'] ? 'x' : '',

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.itinerary'
                ) => $this->travel->tar_itinerary,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.purpose'
                ) => $this->travel->tar_purpose,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.airfare_cost'
                ) => $this->travel->tar_airfare,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.other_cost'
                ) => $this->travel->tar_other_trans,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.lodging_cost'
                ) => $this->travel->tar_lodging,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.registration_cost'
                ) => $this->travel->tar_registration,

                config('docusign.international_travel_authority_request_with_airfare.fields.total_cost') => (
                    ($this->travel->tar_airfare ?? 0) +
                    ($this->travel->tar_other_trans ?? 0) +
                    ($this->travel->tar_lodging ?? 0) +
                    ($this->travel->tar_registration ?? 0)
                ),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.driver_worktag'
                ) => $this->travel->tar_project_number,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.account_code'
                ) => $this->travel->tar_account_code,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.departure_date'
                ) => $this->travel->departure_date->toDateString(),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.return_date'
                ) => $this->travel->return_date->toDateString(),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.dates'
                ) => $this->travel->departure_date->toDateString().' - '
                    .$this->travel->return_date->toDateString(),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.destination'
                ) => $this->travel->destination,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.home_department'
                ) => $this->user->employee_home_department,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.employee_id'
                ) => $this->user->employee_id,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.export_control'
                ) => ($this->travel->export_controlled_technology === true ? 'yes' : 'no'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.export_control_description'
                ) => $this->travel->export_controlled_technology_description,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.embargoed_destination'
                ) => ($this->travel->embargoed_destination === true ? 'yes' : 'no'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.embargoed_countries'
                ) => $this->travel->embargoed_countries,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.biological_materials'
                ) => ($this->travel->biological_materials === true ? 'yes' : 'no'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.biological_materials_description'
                ) => $this->travel->biological_materials_description,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.equipment'
                ) => ($this->travel->equipment === true ? 'yes' : 'no'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.equipment_description'
                ) => $this->travel->equipment_description,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.phone'
                ) => $this->user->phone,

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.non_employee'
                ) => ($this->user->employee_home_department === null ? 'x' : ''),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.employee'
                ) => ($this->user->employee_home_department === null ? '' : 'x'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.non_employee_account'
                ) => ($this->user->employee_home_department === null ? 'x' : ''),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.employee_account'
                ) => ($this->user->employee_home_department === null ? '' : 'x'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.fields.international_travel_justification'
                ) => $this->travel->international_travel_justification,

                config(
                    'docusign.international_travel_authority_request_with_airfare.traveler_name'
                ).'_UserName' => $this->user->full_name,

                config(
                    'docusign.international_travel_authority_request_with_airfare.traveler_name'
                ).'_Email' => $this->user->uid.'@gatech.edu',

                config(
                    'docusign.international_travel_authority_request_with_airfare.ingest_mailbox_name'
                ).'_UserName' => config('app.name'),

                config(
                    'docusign.international_travel_authority_request_with_airfare.ingest_mailbox_name'
                ).'_Email' => config('docusign.ingest_mailbox'),
            ]
        );
    }

    private function getTravelAuthorityRequestPowerFormUrl(): string
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
                ) => $this->user->employee_home_department,

                config(
                    'docusign.domestic_travel_authority_request.fields.employee_id'
                ) => $this->user->employee_id,

                config(
                    'docusign.domestic_travel_authority_request.traveler_name'
                ).'_UserName' => $this->user->full_name,

                config(
                    'docusign.domestic_travel_authority_request.traveler_name'
                ).'_Email' => $this->user->uid.'@gatech.edu',

                config(
                    'docusign.domestic_travel_authority_request.ingest_mailbox_name'
                ).'_UserName' => config('app.name'),

                config(
                    'docusign.domestic_travel_authority_request.ingest_mailbox_name'
                ).'_Email' => config('docusign.ingest_mailbox'),
            ]
        );
    }

    private function getCovidPowerFormUrl(): string
    {
        return config('docusign.covid_risk_acknowledgement.powerform_url').'&'.http_build_query(
            [
                config(
                    'docusign.covid_risk_acknowledgement.fields.covid_dates'
                ) => $this->travel->departure_date->toDateString().' - '
                    .$this->travel->return_date->toDateString(),

                config(
                    'docusign.covid_risk_acknowledgement.fields.covid_destination'
                ) => $this->travel->destination,

                config(
                    'docusign.covid_risk_acknowledgement.traveler_name'
                ).'_UserName' => $this->user->full_name,

                config(
                    'docusign.covid_risk_acknowledgement.traveler_name'
                ).'_Email' => $this->user->uid.'@gatech.edu',

                config(
                    'docusign.covid_risk_acknowledgement.ingest_mailbox_name'
                ).'_UserName' => config('app.name'),

                config(
                    'docusign.covid_risk_acknowledgement.ingest_mailbox_name'
                ).'_Email' => config('docusign.ingest_mailbox'),
            ]
        );
    }
}
