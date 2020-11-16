<?php

declare(strict_types=1);

namespace App;

use BadMethodCallException;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Traits\HasRelationshipObservables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Nova\Actions\Actionable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Represents a user, possibly a member and possibly not.
 *
 * @method static \Illuminate\Database\Eloquent\Builder accessActive() scopes to only users that are access active
 * @method static \Illuminate\Database\Eloquent\Builder active() scopes to only users that are active
 * @method static \Illuminate\Database\Eloquent\Builder findByIdentifier(string $id) finds a user by any identifier
 * @method static \Illuminate\Database\Eloquent\Builder hasOverride() scopes to only users with an active override
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static Builder|User accessInactive()
 * @method static Builder|User inactive()
 * @method static Builder|User newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereAcceptSafetyAgreement($value)
 * @method static Builder|User whereAccessOverrideById($value)
 * @method static Builder|User whereAccessOverrideUntil($value)
 * @method static Builder|User whereApiToken($value)
 * @method static Builder|User whereAutodeskEmail($value)
 * @method static Builder|User whereAutodeskInvitePending($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCreateReason($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmergencyContactName($value)
 * @method static Builder|User whereEmergencyContactPhone($value)
 * @method static Builder|User whereEthnicity($value)
 * @method static Builder|User whereExistsInSums($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereFullName($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereGithubInvitePending($value)
 * @method static Builder|User whereGithubUsername($value)
 * @method static Builder|User whereGmailAddress($value)
 * @method static Builder|User whereGraduationSemester($value)
 * @method static Builder|User whereGtDirGUID($value)
 * @method static Builder|User whereGtEmail($value)
 * @method static Builder|User whereGtid($value)
 * @method static Builder|User whereHasEverLoggedIn($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIsServiceAccount($value)
 * @method static Builder|User whereJoinSemester($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereMiddleName($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePersonalEmail($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePoloSize($value)
 * @method static Builder|User wherePreferredName($value)
 * @method static Builder|User wherePrimaryAffiliation($value)
 * @method static Builder|User whereResumeDate($value)
 * @method static Builder|User whereShirtSize($value)
 * @method static Builder|User whereSlackId($value)
 * @method static Builder|User whereUid($value)
 * @method static Builder|User whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property bool $exists_in_sums
 * @property bool $github_invite_pending
 * @property bool $clickup_invite_pending
 * @property bool $autodesk_invite_pending
 * @property bool $has_ever_logged_in whether the user has ever logged in with CAS
 * @property bool $is_active whether the user is currently active
 * @property bool $is_service_account whether the user is a service account (vs human)
 * @property Carbon|null $accept_safety_agreement
 * @property Carbon|null $access_override_until
 * @property Carbon|null $deleted_at
 * @property Carbon|null $resume_date
 * @property int $gtid
 * @property int $id
 * @property int|null $access_override_by_id
 * @property int|null $clickup_id
 * @property string $create_reason
 * @property string $first_name
 * @property string $gt_email
 * @property string $last_name
 * @property string $name the display name for this user
 * @property string $uid
 * @property string|null $api_token
 * @property string|null $clickup_email
 * @property string|null $autodesk_email
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $ethnicity
 * @property string|null $gender
 * @property string|null $github_username
 * @property string|null $gmail_address
 * @property string|null $graduation_semester
 * @property string|null $gtDirGUID
 * @property string|null $join_semester
 * @property string|null $middle_name
 * @property string|null $personal_email
 * @property string|null $phone
 * @property string|null $polo_size
 * @property string|null $preferred_first_name
 * @property string|null $preferred_name
 * @property string|null $primary_affiliation
 * @property string|null $shirt_size
 * @property string|null $slack_id
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Attendance> $attendance
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\ClassStanding> $classStanding
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\DuesTransaction> $dues
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\DuesTransaction> $duesTransactions
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\DuesTransaction> $paidDues
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Event> $events
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Major> $majors
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\RecruitingVisit> $recruitingVisits
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Rsvp> $rsvps
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Team> $manages
 * @property-read \Illuminate\Database\Eloquent\Collection $teams
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection $roles
 * @property-read bool $is_access_active
 * @property-read int|null $actions_count
 * @property-read int|null $attendance_count
 * @property-read int|null $class_standing_count
 * @property-read int|null $dues_count
 * @property-read int|null $dues_transactions_count
 * @property-read int|null $events_count
 * @property-read int|null $majors_count
 * @property-read int|null $manages_count
 * @property-read int|null $notifications_count
 * @property-read int|null $paid_dues_count
 * @property-read int|null $permissions_count
 * @property-read int|null $recruiting_visits_count
 * @property-read int|null $roles_count
 * @property-read int|null $rsvps_count
 * @property-read int|null $teams_count
 * @property-read string $full_name
 * @property-read User $accessOverrideBy
 */
class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;
    use HasRoles;
    use Actionable;
    use HasBelongsToManyEvents;
    use HasRelationshipObservables;

    private const MAJOR_ENTITLEMENT_PREFIX = '/gt/gtad/gt_resources/stu_majorgroups/';
    private const MAJOR_ENTITLEMENT_PREFIX_LENGTH = 38;

    // phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingAfter
    private const STANDING_ENTITLEMENT_PREFIX =
        '/gt/central/services/office365/gtad-attributes/gtad-extensionattribute14=office365:';
    // phpcs:enable
    private const STANDING_ENTITLEMENT_PREFIX_LENGTH = 83;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'name',
        'full_name',
        'preferred_first_name',
        'is_active',
        'is_access_active',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'accept_safety_agreement',
        'access_override_until',
        'resume_date',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'uid',
        'first_name',
        'last_name',
        'gt_email',
        'name',
        'gtid',
        'api_token',
        'full_name',
        'is_active',
        'access_active',
        'needs_payment',
        'deleted_at',
        'created_at',
        'updated_at',
        'access_override_until',
        'access_override_by_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<string>
     */
    protected $hidden = ['api_token', 'gender', 'ethnicity', 'dues'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'github_invite_pending' => 'boolean',
        'clickup_invite_pending' => 'boolean',
        'autodesk_invite_pending' => 'boolean',
        'exists_in_sums' => 'boolean',
        'has_ever_logged_in' => 'boolean',
        'is_service_account' => 'boolean',
    ];

    /**
     *  Get the recruiting visits associated with this user.
     */
    public function recruitingVisits(): HasMany
    {
        return $this->hasMany(RecruitingVisit::class);
    }

    /**
     *  Get the attendance records associated with this user.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'gtid', 'gtid');
    }

    /**
     *  Get the teams that this user manages.
     */
    public function manages(): HasMany
    {
        return $this->hasMany(Team::class, 'project_manager_id');
    }

    /**
     *  Get the Teams that this User is a member of.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    /**
     * Check membership status for a given team.
     *
     * @param \App\Team $team Team ID
     *
     * @return bool Whether or not user is a member of the given team
     */
    public function memberOfTeam(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    /**
     * Get the name associated with the User.
     */
    public function getNameAttribute(): string
    {
        $first = $this->preferred_name ?? $this->first_name;

        return implode(' ', [$first, $this->last_name]);
    }

    /**
     * Get the preferred first name associated with the User.
     */
    public function getPreferredFirstNameAttribute(): ?string
    {
        return $this->preferred_name ?? $this->first_name;
    }

    /**
     * Set the preferred first name associated with the User. Stores null if preferred name matches legal name.
     */
    public function setPreferredFirstNameAttribute(?string $preferred_name): void
    {
        $this->attributes['preferred_name'] = $preferred_name === $this->first_name ? null : $preferred_name;
    }

    /**
     * Get the full name associated with the User.
     */
    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]));
    }

    /*
     * Get the DuesTransactions belonging to the User
     */
    public function dues(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /*
     * Get the DuesTransactions belonging to the User
     */
    public function duesTransactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /*
     * Get the DuesTransactions belonging to the User
     */
    public function paidDues(): HasMany
    {
        return $this->hasMany(DuesTransaction::class)->paid();
    }

    /**
     * Get the events organized by the User.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Get the RSVPs belonging to the User.
     */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class)->whereNull('major_user.deleted_at')->withTimestamps();
    }

    public function classStanding(): BelongsToMany
    {
        return $this->belongsToMany(
            ClassStanding::class
        )->whereNull(
            'class_standing_user.deleted_at'
        )->withTimestamps();
    }

    /**
     * Route notifications for the mail channel.
     * Send to GT email when present and fall back to personal email if not.
     */
    public function routeNotificationForMail(): string
    {
        return $this->gt_email;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getRememberToken(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint

    public function setRememberToken($value): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    // phpcs:enable

    public function getRememberTokenName(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'recruitingVisits' => 'recruiting-visits',
            'teams' => 'teams-membership',
            'dues' => 'dues-transactions',
            'events' => 'events',
            'rsvps' => 'rsvps',
            'roles' => 'roles-and-permissions',
            'permissions' => 'roles-and-permissions',
        ];
    }

    /**
     * Get the is_active flag for the User.
     */
    public function getIsActiveAttribute(): bool
    {
        return 0 !== self::where('id', $this->id)->active()->count();
    }

    /**
     * Get the access_active flag for the User.
     */
    public function getIsAccessActiveAttribute(): bool
    {
        return 0 !== self::where('id', $this->id)->accessActive()->count();
    }

    /**
     * Scope a query to automatically determine user identifier.
     */
    public function scopeFindByIdentifier(Builder $query, string $id): Builder
    {
        if (is_numeric($id) && 9 === strlen($id) && '9' === $id[0]) {
            return $query->where('gtid', $id);
        }

        if (is_numeric($id)) {
            return $query->where('id', $id);
        }

        return $query->where('uid', $id);
    }

    /**
     * Scope a query to automatically include only active members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('dues', static function (Builder $q): void {
            $q->paid()->current();
        });
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereDoesntHave('dues', static function (Builder $q): void {
            $q->paid()->current();
        });
    }

    /**
     * Scope a query to automatically include only access active members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     */
    public function scopeAccessActive(Builder $query): Builder
    {
        return $query->whereHas('dues', static function (Builder $q): void {
            $q->paid()->accessCurrent();
        })->orWhere('access_override_until', '>=', date('Y-m-d H:i:s'));
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     */
    public function scopeAccessInactive(Builder $query): Builder
    {
        return $query->whereDoesntHave('dues', static function (Builder $q): void {
            $q->paid()->accessCurrent();
        })->where(static function (Builder $query): void {
            $query->where('access_override_until', '<=', date('Y-m-d H:i:s'))->orWhereNull('access_override_until');
        });
    }

    /**
     * Scope a query to automatically include only members with access overrides.
     */
    public function scopeHasOverride(Builder $query): Builder
    {
        return $query->inactive()->where('access_override_until', '>', now());
    }

    public function accessOverrideBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'access_override_by_id');
    }

    /**
     * Synchronizes major relationship with a given list of gtAccountEntitlements.
     *
     * @param array<string>  $accountEntitlements
     */
    public function syncMajorsFromAccountEntitlements(array $accountEntitlements): void
    {
        $current_major_ids = $this->majors()->pluck('majors.id')->toArray();

        $new_major_ids = [];

        foreach ($accountEntitlements as $entitlement) {
            if (self::MAJOR_ENTITLEMENT_PREFIX !== substr($entitlement, 0, self::MAJOR_ENTITLEMENT_PREFIX_LENGTH)) {
                continue;
            }

            $new_major_ids[] = Major::findOrCreateFromGtadGroup(
                // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
                substr(
                    $entitlement,
                    self::MAJOR_ENTITLEMENT_PREFIX_LENGTH
                )
            )->id;
        }

        foreach ($new_major_ids as $new_major_id) {
            if (in_array($new_major_id, $current_major_ids, true)) {
                continue;
            }

            $this->majors()->attach($new_major_id);
        }

        foreach ($current_major_ids as $current_major_id) {
            if (in_array($current_major_id, $new_major_ids, true)) {
                continue;
            }

            $this->majors()->updateExistingPivot($current_major_id, ['deleted_at' => Carbon::now()]);
        }
    }

    /**
     * Synchronizes major relationship with a given list of gtAccountEntitlements.
     *
     * @param array<string>  $accountEntitlements
     */
    public function syncClassStandingFromAccountEntitlements(array $accountEntitlements): int
    {
        $current_class_standings = $this->classStanding()->pluck('class_standings.id')->toArray();

        $new_class_standings = [];

        foreach ($accountEntitlements as $entitlement) {
            if (self::STANDING_ENTITLEMENT_PREFIX !== substr(
                $entitlement,
                0,
                self::STANDING_ENTITLEMENT_PREFIX_LENGTH
            )) {
                continue;
            }

            $standing_name = explode(
                '-',
                substr(
                    $entitlement,
                    self::STANDING_ENTITLEMENT_PREFIX_LENGTH
                )
            )[0];

            $new_class_standings[] = ClassStanding::findOrCreateFromName($standing_name)->id;
        }

        foreach ($new_class_standings as $new_class_standing) {
            if (in_array($new_class_standing, $current_class_standings, true)) {
                continue;
            }

            $this->classStanding()->attach($new_class_standing);
        }

        foreach ($current_class_standings as $current_class_standing) {
            if (in_array($current_class_standing, $new_class_standings, true)) {
                continue;
            }

            $this->classStanding()->updateExistingPivot($current_class_standing, ['deleted_at' => Carbon::now()]);
        }

        return count($new_class_standings);
    }
}
