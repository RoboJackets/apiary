<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property-read \App\Models\User $primaryContact
 * @property \Illuminate\Support\Carbon $departure_date
 * @property \Illuminate\Support\Carbon $return_date
 * @property int $fee_amount
 * @property string $included_with_fee
 * @property string|null $not_included_with_fee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\TravelAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property string|null $tar_itinerary
 * @property string|null $tar_purpose
 * @property int|null $tar_airfare
 * @property int|null $tar_other_trans
 * @property int|null $tar_lodging
 * @property int|null $tar_registration
 * @property string|null $tar_project_number
 * @property string|null $tar_account_code
 * @property bool $is_international
 * @property bool $export_controlled_technology
 * @property string $export_controlled_technology_description
 * @property bool $embargoed_destination
 * @property string $embargoed_countries
 * @property bool $biological_materials
 * @property string $biological_materials_description
 * @property bool $equipment
 * @property string $equipment_description
 * @property string $international_travel_justification
 * @property bool $payment_completion_email_sent
 * @property bool $form_completion_email_sent
 * @property array|null $airfare_policy
 * @property int|null $meal_per_diem
 * @property int|null $car_rental_cost
 * @property string|null $hotel_name
 * @property string|null $department_number
 * @property array|null $forms
 * @property-read bool $needs_airfare_form
 * @property-read bool $needs_travel_information_form
 * @property-read bool $needs_docusign
 * @property-read bool $assignments_need_forms
 * @property-read bool $assignments_need_payment
 *
 * @method static \Database\Factories\TravelFactory factory(...$parameters)
 * @method static Builder|Travel newModelQuery()
 * @method static Builder|Travel newQuery()
 * @method static \Illuminate\Database\Query\Builder|Travel onlyTrashed()
 * @method static Builder|Travel query()
 * @method static Builder|Travel whereCreatedAt($value)
 * @method static Builder|Travel whereDeletedAt($value)
 * @method static Builder|Travel whereDepartureDate($value)
 * @method static Builder|Travel whereDestination($value)
 * @method static Builder|Travel whereFeeAmount($value)
 * @method static Builder|Travel whereId($value)
 * @method static Builder|Travel whereIncludedWithFee($value)
 * @method static Builder|Travel whereName($value)
 * @method static Builder|Travel whereNotIncludedWithFee($value)
 * @method static Builder|Travel wherePrimaryContactUserId($value)
 * @method static Builder|Travel whereReturnDate($value)
 * @method static Builder|Travel whereTarAccountCode($value)
 * @method static Builder|Travel whereTarAirfare($value)
 * @method static Builder|Travel whereTarItinerary($value)
 * @method static Builder|Travel whereTarLodging($value)
 * @method static Builder|Travel whereTarOtherTrans($value)
 * @method static Builder|Travel whereTarProjectNumber($value)
 * @method static Builder|Travel whereTarPurpose($value)
 * @method static Builder|Travel whereTarRegistration($value)
 * @method static Builder|Travel whereUpdatedAt($value)
 * @method static Builder|Travel whereBiologicalMaterials($value)
 * @method static Builder|Travel whereBiologicalMaterialsDescription($value)
 * @method static Builder|Travel whereEmbargoedCountries($value)
 * @method static Builder|Travel whereEmbargoedDestination($value)
 * @method static Builder|Travel whereEquipment($value)
 * @method static Builder|Travel whereEquipmentDescription($value)
 * @method static Builder|Travel whereExportControlledTechnology($value)
 * @method static Builder|Travel whereExportControlledTechnologyDescription($value)
 * @method static Builder|Travel whereInternationalTravelJustification($value)
 * @method static Builder|Travel whereIsInternational($value)
 * @method static Builder|Travel whereFormCompletionEmailSent($value)
 * @method static Builder|Travel wherePaymentCompletionEmailSent($value)
 * @method static \Illuminate\Database\Query\Builder|Travel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Travel withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
 */
class Travel extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    public const TRAVEL_INFORMATION_FORM_KEY = 'travel_information';

    public const AIRFARE_REQUEST_FORM_KEY = 'airfare_request';

    public const FORM_LABELS = [
        self::TRAVEL_INFORMATION_FORM_KEY => 'Travel Information Form',
        self::AIRFARE_REQUEST_FORM_KEY => 'Airfare Request Form',
    ];

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
        'payment_completion_email_sent' => 'boolean',
        'form_completion_email_sent' => 'boolean',
        'is_international' => 'boolean',
        'export_controlled_technology' => 'boolean',
        'embargoed_destination' => 'boolean',
        'biological_materials' => 'boolean',
        'equipment' => 'boolean',
        'airfare_policy' => 'array',
        'forms' => 'array',
    ];

    /**
     * The attributes that Nova might think can be used for filtering, but actually can't.
     */
    public const DO_NOT_FILTER_ON = [
        'user_id',
    ];

    /**
     * Get the primary contact for this travel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Travel>
     */
    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_contact_user_id');
    }

    /**
     * Get the assignments for this travel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\TravelAssignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TravelAssignment::class);
    }

    public function getAssignmentsNeedPaymentAttribute(): bool
    {
        return $this->assignments()->unpaid()->exists();
    }

    public function getAssignmentsNeedFormsAttribute(): bool
    {
        return $this->assignments()->needDocuSign()->exists();
    }

    public function getNeedsDocusignAttribute(): bool
    {
        return $this->needs_travel_information_form || $this->needs_airfare_form;
    }

    public function getNeedsTravelInformationFormAttribute(): bool
    {
        return $this->getNeedsFormAttribute(self::TRAVEL_INFORMATION_FORM_KEY);
    }

    public function getNeedsAirfareFormAttribute(): bool
    {
        return $this->getNeedsFormAttribute(self::AIRFARE_REQUEST_FORM_KEY);
    }

    private function getNeedsFormAttribute(string $form): bool
    {
        return $this->forms !== null && array_key_exists($form, $this->forms) && $this->forms[$form] === true;
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Travel>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Travel>
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
