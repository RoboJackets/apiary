<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Models;

use BadMethodCallException;
use Carbon\CarbonImmutable;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Traits\HasRelationshipObservables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Auth\Impersonatable;
use Laravel\Nova\Notifications\Notification;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * Represents a user, possibly a member and possibly not.
 *
 * @property int $id
 * @property string $uid
 * @property int $gtid
 * @property string|null $github_username
 * @property string|null $gmail_address
 * @property string|null $clickup_email
 * @property int|null $clickup_id
 * @property bool $clickup_invite_pending
 * @property string $gt_email
 * @property string $first_name
 * @property string $last_name
 * @property string|null $preferred_name
 * @property string|null $phone
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $join_semester
 * @property string|null $graduation_semester
 * @property string|null $shirt_size
 * @property string|null $polo_size
 * @property string|null $gender
 * @property string|null $ethnicity
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $access_override_until
 * @property int|null $access_override_by_id user_id of the user who entered access override
 * @property Carbon|null $resume_date
 * @property bool $github_invite_pending
 * @property bool $exists_in_sums
 * @property string $create_reason
 * @property bool $has_ever_logged_in
 * @property bool $is_service_account
 * @property string|null $primary_affiliation
 * @property string|null $gtDirGUID
 * @property bool $buzzcard_access_opt_out
 * @property string|null $email_suppression_reason
 * @property int|null $employee_id
 * @property string|null $employee_home_department
 * @property-read User|null $accessOverrideBy
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\TravelAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read int|null $attendance_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\ClassStanding> $classStanding
 * @property-read int|null $class_standing_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $dues
 * @property-read int|null $dues_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesPackage> $duesPackages
 * @property-read int|null $dues_packages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $duesTransactions
 * @property-read int|null $dues_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Event> $events
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DocuSignEnvelope> $envelopes
 * @property-read int|null $envelopes_count
 * @property-read int|null $events_count
 * @property-read \App\Models\TravelAssignment|null $current_travel_assignment
 * @property-read string $full_name
 * @property-read bool $has_active_override
 * @property-read bool $has_ordered_polo
 * @property-read bool $is_access_active
 * @property-read bool $is_active
 * @property-read bool $is_student
 * @property-read string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Major> $majors
 * @property string|null $preferred_first_name
 * @property-read \App\Models\SelfServiceAccessOverrideEligibility $self_service_override_eligibility
 * @property-read bool $should_receive_email
 * @property-read bool $signed_latest_agreement
 * @property-read int|null $majors_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Team> $manages
 * @property-read int|null $manages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $paidDues
 * @property-read \Illuminate\Database\Eloquent\Collection|array<Notification> $novaNotifications
 * @property-read int|null $nova_notifications_count
 * @property-read int|null $paid_dues_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Rsvp> $rsvps
 * @property-read int|null $rsvps_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Signature> $signatures
 * @property-read int|null $signatures_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\OAuth2Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\OAuth2AccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\User|null $manager
 * @property-read bool $has_emergency_contact_information
 *
 * @method static Builder|User accessActive()
 * @method static Builder|User accessInactive(\Carbon\CarbonImmutable|null $asOfTimestamp)
 * @method static Builder|User active()
 * @method static Builder|User buzzCardAccessEligible()
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User findByIdentifier(string $id)
 * @method static Builder|User hasOverride()
 * @method static Builder|User inactive()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static QueryBuilder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereAccessOverrideById($value)
 * @method static Builder|User whereAccessOverrideUntil($value)
 * @method static Builder|User whereBuzzcardAccessOptOut($value)
 * @method static Builder|User whereClickupEmail($value)
 * @method static Builder|User whereClickupId($value)
 * @method static Builder|User whereClickupInvitePending($value)
 * @method static Builder|User whereCreateReason($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmailSuppressionReason($value)
 * @method static Builder|User whereEmergencyContactName($value)
 * @method static Builder|User whereEmergencyContactPhone($value)
 * @method static Builder|User whereEmployeeHomeDepartment($value)
 * @method static Builder|User whereEmployeeId($value)
 * @method static Builder|User whereEthnicity($value)
 * @method static Builder|User whereExistsInSums($value)
 * @method static Builder|User whereFirstName($value)
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
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePoloSize($value)
 * @method static Builder|User wherePreferredName($value)
 * @method static Builder|User wherePrimaryAffiliation($value)
 * @method static Builder|User whereResumeDate($value)
 * @method static Builder|User whereShirtSize($value)
 * @method static Builder|User whereUid($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static QueryBuilder|User withTrashed()
 * @method static QueryBuilder|User withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property string|null $docusign_access_token
 * @property Carbon|null $docusign_access_token_expires_at
 * @property string|null $docusign_refresh_token
 * @property Carbon|null $docusign_refresh_token_expires_at
 *
 * @method static Builder|User whereDocusignAccessToken($value)
 * @method static Builder|User whereDocusignAccessTokenExpiresAt($value)
 * @method static Builder|User whereDocusignRefreshToken($value)
 * @method static Builder|User whereDocusignRefreshTokenExpiresAt($value)
 *
 * @property string|null $parent_guardian_name
 * @property string|null $parent_guardian_email
 * @property-read bool $needs_parent_or_guardian_signature
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
 */
class User extends Authenticatable
{
    use Actionable;
    use HasApiTokens;
    use HasBelongsToManyEvents;
    use HasFactory;
    use HasPermissions;
    use HasRelationshipObservables;
    use HasRoles;
    use Impersonatable;
    use Notifiable;
    use Searchable;
    use SoftDeletes;

    private const MAJOR_REGEX = '/(?P<college>[A-Z])\/(?P<school>[A-Z0-9]+)\/(?P<major>[A-Z]+)/';

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
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'emergency_contact_name',
        'emergency_contact_phone',
        'phone',
        'preferred_first_name',
        'shirt_size',
        'polo_size',
        'graduation_semester',
        'clickup_email',
        'ethnicity',
        'gender',
        'clickup_invite_pending',
        'clickup_id',
        'exists_in_sums',
        'github_invite_pending',
        'legal_gender',
        'date_of_birth',
        'delta_skymiles_number',
        'legal_middle_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<string>
     */
    protected $hidden = [
        'gender',
        'ethnicity',
        'dues',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'access_override_until' => 'datetime',
        'resume_date' => 'datetime',
        'github_invite_pending' => 'boolean',
        'clickup_invite_pending' => 'boolean',
        'exists_in_sums' => 'boolean',
        'has_ever_logged_in' => 'boolean',
        'is_service_account' => 'boolean',
        'buzzcard_access_opt_out' => 'boolean',
        'docusign_access_token_expires_at' => 'datetime',
        'docusign_refresh_token_expires_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    /**
     * The attributes that Nova might think can be used for filtering, but actually can't.
     */
    public const DO_NOT_FILTER_ON = [
        'dues_package_id',
        'trip_id',
        'merchandise_id',
        'user_id',
    ];

    /**
     * List of valid shirt sizes and display names for them.
     *
     * @var array<string,string>
     *
     * @phan-read-only
     */
    public static array $shirt_sizes = [
        's' => 'Small',
        'm' => 'Medium',
        'l' => 'Large',
        'xl' => 'Extra-Large',
        'xxl' => 'XXL',
        'xxxl' => 'XXXL',
    ];

    /**
     * List of class-standing-like strings to ignore from eduPersonScopedAffiliation.
     *
     * @var array<string>
     *
     * @phan-read-only
     */
    public static array $ignore_class_standings = [
        'account',
        'credit',
        'former',
        'graduate',
        'undergrad',
    ];

    protected string $guard_name = 'web';

    public const RELATIONSHIP_PERMISSIONS = [
        'teams' => 'read-teams-membership',
        'dues' => 'read-dues-transactions',
        'events' => 'read-events',
        'rsvps' => 'read-rsvps',
        'roles' => 'read-roles-and-permissions',
        'permissions' => 'read-roles-and-permissions',
        'assignments.travel' => 'manage-travel',
        'merchandise.merchandise' => 'read-merchandise',
        'merchandise.providedBy' => 'read-users',
    ];

    /**
     * Get the attendance records associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Attendance>
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'gtid', 'gtid');
    }

    /**
     * Get the teams that this user manages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Team>
     */
    public function manages(): HasMany
    {
        return $this->hasMany(Team::class, 'project_manager_id');
    }

    /**
     * Get the Teams that this User is a member of.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Team>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    /**
     * Check membership status for a given team.
     *
     * @param  \App\Models\Team  $team  Team ID
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
        return implode(' ', array_filter([$this->first_name, $this->last_name]));
    }

    /**
     * Get whether this user is a current student.
     */
    public function getIsStudentAttribute(): bool
    {
        return $this->primary_affiliation === 'student'
            && $this->duesTransactions()->paid()->whereHas('package', static function (Builder $query): void {
                $query->where('restricted_to_students', false);
            })->doesntExist();
    }

    /**
     * Get the DuesTransactions belonging to the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesTransaction>
     */
    public function dues(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the DocuSign envelopes signed by the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DocuSignEnvelope>
     */
    public function envelopes(): HasMany
    {
        return $this->hasMany(DocuSignEnvelope::class, 'signed_by');
    }

    /**
     * Get the DuesTransactions belonging to the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesTransaction>
     */
    public function duesTransactions(): HasMany
    {
        return $this->hasMany(DuesTransaction::class);
    }

    /**
     * Get the DuesTransactions belonging to the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DuesTransaction>
     */
    public function paidDues(): HasMany
    {
        return $this->hasMany(DuesTransaction::class)->paid();
    }

    /**
     * Get the DuesPackages belonging to the User through DuesTransactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\DuesPackage>
     */
    public function duesPackages(): HasManyThrough
    {
        return $this->hasManyThrough(
            DuesPackage::class,
            DuesTransaction::class,
            'user_id',
            'id',
            'id',
            'dues_package_id'
        );
    }

    /**
     * Get the events organized by the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Event>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Get the RSVPs belonging to the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Rsvp>
     */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    /**
     * Get the majors for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Major>
     */
    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class)->whereNull('major_user.deleted_at')->withTimestamps();
    }

    /**
     * Get the class standings for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\ClassStanding>
     */
    public function classStanding(): BelongsToMany
    {
        return $this->belongsToMany(ClassStanding::class)->whereNull(
            'class_standing_user.deleted_at'
        )->withTimestamps();
    }

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

    public function setRememberToken($value): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getRememberTokenName(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Graduation Semester is a 6-digit code by default.
     * To retrieve a more readable form, this method obtains the
     * code for this User and translates it to the format "[season] yyyy".
     */
    public function getHumanReadableGraduationSemesterAttribute(): string
    {
        $semester = $this->graduation_semester;
        if ($semester === null || preg_match('/^[0-9]{4}0[258]$/', $semester) === 0) {
            return '';
        }

        $semcode = substr($semester, 4);
        $year = substr($semester, 0, 4);
        $season = '';
        switch ($semcode) {
            case '08':
                $season .= 'Fall ';
                break;
            case '02':
                $season .= 'Spring ';
                break;
            case '05':
                $season .= 'Summer ';
                break;
            default:
                return '';
        }

        return $season.$year;
    }

    /**
     * Get the is_active flag for the User.
     */
    public function getIsActiveAttribute(): bool
    {
        return self::where('id', $this->id)->active()->count() !== 0;
    }

    /**
     * Get the access_active flag for the User.
     */
    public function getIsAccessActiveAttribute(): bool
    {
        return self::where('id', $this->id)->accessActive()->count() !== 0;
    }

    /**
     * Get whether the user has ever chosen a polo wtih their dues.
     */
    public function getHasOrderedPoloAttribute(): bool
    {
        return $this->paidDues()->whereHas('merchandise', static function (Builder $q): void {
            $q->where('name', 'like', 'Polo %');
        })->exists();
    }

    /**
     * Scope a query to automatically determine user identifier.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function scopeFindByIdentifier(Builder $query, string $id): Builder
    {
        if (is_numeric($id) && strlen($id) === 9 && $id[0] === '9') {
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function scopeAccessActive(Builder $query): Builder
    {
        return $query->whereHas('dues', static function (Builder $q): void {
            $q->paid()->accessCurrent();
        })->orWhere('access_override_until', '>=', now());
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function scopeAccessInactive(Builder $query, ?CarbonImmutable $asOfTimestamp = null): Builder
    {
        return $query->whereDoesntHave('dues', static function (Builder $q) use ($asOfTimestamp): void {
            $q->paid()->accessCurrent($asOfTimestamp);
        })->where(static function (Builder $query) use ($asOfTimestamp): void {
            $query->where(
                'access_override_until',
                '<=',
                $asOfTimestamp ?? CarbonImmutable::now()
            )->orWhereNull('access_override_until');
        });
    }

    /**
     * Scope a query to automatically include only those eligible to be granted BuzzCard access.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function scopeBuzzCardAccessEligible(Builder $query): Builder
    {
        return $query->accessActive()
            ->where('buzzcard_access_opt_out', false)
            ->whereIn('primary_affiliation', ['student', 'faculty', 'staff'])
            ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                $q->where('restricted_to_students', false);
            });
    }

    /**
     * Scope a query to automatically include only members with access overrides.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function scopeHasOverride(Builder $query): Builder
    {
        return $query->inactive()->where('access_override_until', '>', now());
    }

    /**
     * Get the user that applied an access override.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\User>
     */
    public function accessOverrideBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'access_override_by_id');
    }

    /**
     * Synchronizes major relationship with a given list of gtAccountEntitlements.
     *
     * @param  array<string>  $gtCurriculum
     */
    public function syncMajorsFromGtCurriculum(array $gtCurriculum): int
    {
        $current_major_ids = $this->majors()->pluck('majors.id')->toArray();

        $new_major_ids = [];

        foreach ($gtCurriculum as $curriculum) {
            $matches = [];

            if (preg_match(self::MAJOR_REGEX, $curriculum, $matches) !== 1) {
                continue;
            }

            $new_major_ids[] = Major::findOrCreateFromGtadGroup($matches['major'].'_'.$matches['school'])->id;
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

        return count($new_major_ids);
    }

    /**
     * Synchronizes class standing relationships with a given list of eduPersonScopedAffiliation.
     *
     * @param  array<string>  $eduPersonScopedAffiliation
     */
    public function syncClassStandingFromEduPersonScopedAffiliation(array $eduPersonScopedAffiliation): int
    {
        $current_class_standings = $this->classStanding()->pluck('class_standings.id')->toArray();

        $new_class_standings = [];

        foreach ($eduPersonScopedAffiliation as $scopedAffiliation) {
            $affiliation_and_scope = explode('@', $scopedAffiliation);

            if ($affiliation_and_scope[1] !== 'gt') {
                continue;
            }

            if (! str_ends_with($affiliation_and_scope[0], '-student')) {
                continue;
            }

            $standing_name = explode('-', $affiliation_and_scope[0])[0];

            if (in_array($standing_name, self::$ignore_class_standings, true)) {
                continue;
            }

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

    /**
     * Get the Signatures for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Signature>
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function getSignedLatestAgreementAttribute(): bool
    {
        $query = $this
            ->signatures()
            ->where('complete', true)
            ->where(
                'membership_agreement_template_id',
                static function (QueryBuilder $query): void {
                    $query->select('id')
                        ->from('membership_agreement_templates')
                        ->orderByDesc('updated_at')
                        ->limit(1);
                }
            )->whereHas('envelope', static function (Builder $query): void {
                $query->where('complete', true);
            });

        return $query->exists();
    }

    /**
     * Get the Nova notifications for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Laravel\Nova\Notifications\Notification>
     */
    public function novaNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get the TravelAssignments for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\TravelAssignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TravelAssignment::class);
    }

    public function getShouldReceiveEmailAttribute(): bool
    {
        return $this->email_suppression_reason === null;
    }

    /**
     * Determine if the user can impersonate another user.
     */
    public function canImpersonate(): bool
    {
        return $this->can('impersonate-users');
    }

    /**
     * Determine if the user can be impersonated.
     */
    public function canBeImpersonated(): bool
    {
        return ! $this->is_service_account;
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query
            ->withCount('attendance')
            ->withCount('envelopes')
            ->withCount('signatures')
            ->with('classStanding')
            ->with('majors')
            ->with('teams')
            ->with('permissions')
            ->with('roles');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $array['revenue_total'] = intval(Payment::selectRaw(
            '(coalesce(sum(payments.amount),0) - coalesce(sum(payments.processing_fee),0)) as revenue'
        )->leftJoin('dues_transactions', static function (JoinClause $join): void {
            $join->on('dues_transactions.id', '=', 'payable_id')
                ->where('payments.amount', '>', 0)
                ->where('payments.method', '!=', 'waiver')
                ->where('payments.payable_type', DuesTransaction::getMorphClassStatic())
                ->whereNull('payments.deleted_at');
        })->leftJoin('travel_assignments', static function (JoinClause $join): void {
            $join->on('travel_assignments.id', '=', 'payable_id')
                ->where('payments.amount', '>', 0)
                ->where('payments.method', '!=', 'waiver')
                ->where('payments.payable_type', TravelAssignment::getMorphClassStatic())
                ->whereNull('payments.deleted_at');
        })->where('travel_assignments.user_id', '=', $this->id)
            ->orWhere('dues_transactions.user_id', '=', $this->id)
            ->get()[0]['revenue']);

        if (! array_key_exists('attendance_count', $array)) {
            $array['attendance_count'] = $this->attendance()->count();
        }

        if (! array_key_exists('envelopes_count', $array)) {
            $array['envelopes_count'] = $this->envelopes()->count();
        }

        if (! array_key_exists('signatures_count', $array)) {
            $array['signatures_count'] = $this->signatures()->count();
        }

        $array['class_standing_id'] = $this->classStanding->modelKeys();

        $array['major_id'] = $this->majors->modelKeys();

        $array['team_id'] = $this->teams->modelKeys();

        $array['permission_id'] = $this->permissions->modelKeys();

        $array['role_id'] = $this->roles->modelKeys();

        return $array;
    }

    public function getHasActiveOverrideAttribute(): bool
    {
        return ! $this->is_active && $this->access_override_until && $this->access_override_until > now();
    }

    public function getSelfServiceOverrideEligibilityAttribute(): SelfServiceAccessOverrideEligibility
    {
        $ineligibleReason = 'Unable to provide a self-service override at this time';

        // phpcs:ignore
        $INELIGIBLE_NO_FUTURE_DUES_PKG = 'Self-service access overrides are currently unavailable because there are '.
            'no dues packages with future access end dates';
        $INELIGIBLE_REQ_CONDS = 'Account and system conditions for self-service override not met';
        // phpcs:ignore
        $INELIGIBLE_REQ_TASKS = 'You have outstanding required tasks that must be completed before receiving your '.
            'self-service override';

        $now = CarbonImmutable::now();
        $nextAccessEndDuesPkg = DuesPackage::where('access_end', '>', $now)->get()->sortBy('access_end')->first();

        $eligible = false;
        $userRectifiable = false; // Simple things the user can do (e.g., attend a team meeting, sign the membership
        // agreement) to become eligible for a self-service access override. As opposed to things the user can't
        // change such as having paid dues in the past
        $overrideEndDate = null;

        // conditions
        $accessNotActive = ! $this->is_access_active;
        $noExistingOverride = $this->access_override_until === null;
        $hasNotPaidDues = ! $this->paidDues()->exists();
        $eligibleDuesPkgExists = $nextAccessEndDuesPkg !== null;

        // tasks
        $attendedTeamMeeting = $this
            ->attendance()
            ->whereAttendableType('team')
            ->whereHasMorph('attendable', [Team::class], static function (Builder $query): void {
                $query->where('self_service_override_eligible', true);
            })
            ->exists();
        $signedLatestAgreement = $this->signed_latest_agreement;

        if ($eligibleDuesPkgExists) {
            $overrideEndDate = new CarbonImmutable($nextAccessEndDuesPkg->access_end);
            $OVERRIDE_MIN_LENGTH_DAYS = 7;
            $OVERRIDE_MAX_LENGTH_DAYS = 60;
            $overrideLengthDays = $now->diffInDays($nextAccessEndDuesPkg->access_end, false);

            if ($overrideLengthDays > $OVERRIDE_MAX_LENGTH_DAYS) {
                $overrideEndDate = $now->addDays($OVERRIDE_MAX_LENGTH_DAYS)->endOfDay();
            } elseif ($overrideLengthDays < $OVERRIDE_MIN_LENGTH_DAYS) {
                $overrideEndDate = $now->addDays($OVERRIDE_MIN_LENGTH_DAYS)->endOfDay();
            }

            if (! $accessNotActive || ! $noExistingOverride || ! $hasNotPaidDues) {
                $ineligibleReason = $INELIGIBLE_REQ_CONDS;
            } elseif (! $attendedTeamMeeting || ! $signedLatestAgreement) {
                $userRectifiable = true;
                $ineligibleReason = $INELIGIBLE_REQ_TASKS;
            } else {
                $eligible = true;
            }
        } else {
            $ineligibleReason = $INELIGIBLE_NO_FUTURE_DUES_PKG;
        }

        return (new SelfServiceAccessOverrideEligibility())
            ->setEligibility($eligible)
            ->setUserRectifiable($userRectifiable)
            ->setIneligibleReason($eligible ? '' : $ineligibleReason)
            ->setRequiredConditions([
                'Access must not be active' => $accessNotActive,
                'Must have no prior dues payments' => $hasNotPaidDues,
                'Must have no previous access override' => $noExistingOverride,
                'Future dues package must exist' => $eligibleDuesPkgExists,
            ])
            ->setRequiredTasks([
                'Sign the membership agreement' => $signedLatestAgreement,
                'Attend a team meeting' => $attendedTeamMeeting,
            ])
            ->setOverrideUntil($overrideEndDate);
    }

    public function getCurrentTravelAssignmentAttribute(): ?TravelAssignment
    {
        $needPayment = $this->assignments()
            ->select('travel_assignments.*')
            ->whereHas(
                'travel',
                static function (Builder $query): void {
                    $query->whereIn('status', ['approved', 'complete'])
                        ->where('fee_amount', '>', 0);
                }
            )
            ->unpaid()
            ->oldest('travel.departure_date')
            ->oldest('travel.return_date')
            ->first();

        if ($needPayment !== null) {
            return $needPayment;
        }

        $needDocuSign = $this->assignments()
            ->select('travel_assignments.*')
            ->whereHas(
                'travel',
                static function (Builder $query): void {
                    $query->whereIn('status', ['approved', 'complete']);
                }
            )
            ->leftJoin('travel', 'travel.id', '=', 'travel_assignments.travel_id')
            ->needDocuSign()
            ->oldest('travel.departure_date')
            ->oldest('travel.return_date')
            ->first();

        if ($needDocuSign !== null) {
            return $needDocuSign;
        }

        // this might be null, but that's fine
        return $this->assignments()
            ->select('travel_assignments.*')
            ->whereHas(
                'travel',
                static function (Builder $query): void {
                    $query->whereIn('status', ['approved', 'complete']);
                }
            )
            ->leftJoin('travel', 'travel.id', '=', 'travel_assignments.travel_id')
            ->oldest('travel.departure_date')
            ->oldest('travel.return_date')
            ->where('travel.return_date', '>=', now())
            ->first();
    }

    public function getManagerAttribute(): ?User
    {
        $teams = Attendance::where('gtid', $this->gtid)
            ->where('attendable_type', Team::getMorphClassStatic())
            ->leftJoin('teams', function (JoinClause $join): void {
                $join->on('teams.id', '=', 'attendance.attendable_id')
                    ->whereNotNull('teams.project_manager_id')
                    ->where('teams.project_manager_id', '!=', $this->id);
            })
            ->whereNotNull('teams.project_manager_id')
            ->groupBy('attendable_id')
            ->select('attendable_id', DB::raw('count(*) as count'), DB::raw('\'team\' as attendable_type'))
            ->orderByDesc('count')
            ->get()
            ->toArray();

        return count($teams) === 0 ? null : Team::whereId($teams[0])->sole()->projectManager;
    }

    public function getHasEmergencyContactInformationAttribute(): bool
    {
        return $this->emergency_contact_name !== null &&
            $this->emergency_contact_phone !== null &&
            $this->phone !== $this->emergency_contact_phone;
    }

    public function getNeedsParentOrGuardianSignatureAttribute(): bool
    {
        return $this->parent_guardian_name !== null && $this->parent_guardian_email !== null;
    }

    /**
     * Get the DuesTransactionMerchandise objects for this user.
     *
     * @return HasManyThrough<DuesTransactionMerchandise>
     */
    public function merchandise(): HasManyThrough
    {
        return $this->hasManyThrough(
            DuesTransactionMerchandise::class,
            DuesTransaction::class,
            'user_id',
            'dues_transaction_id',
            'id',
            'id'
        );
    }
}
