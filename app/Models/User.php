<?php

declare(strict_types=1);

namespace App\Models;

use Adldap\Laravel\Facades\Adldap;
use BadMethodCallException;
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
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Auth\Impersonatable;
use Laravel\Nova\Notifications\Notification;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use RoboJackets\MeilisearchIndexSettingsHelper\FirstNameSynonyms;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
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
 * @property string|null $autodesk_email
 * @property bool $autodesk_invite_pending
 * @property string $gt_email
 * @property string $first_name
 * @property string|null $middle_name
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
 * @property string|null $preferred_first_name
 * @property-read User|null $accessOverrideBy
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
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
 * @property-read int|null $events_count
 * @property-read string $full_name
 * @property-read bool $has_ordered_polo
 * @property-read bool $is_access_active
 * @property-read bool $is_active
 * @property-read string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Major> $majors
 * @property-read int|null $majors_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Team> $manages
 * @property-read int|null $manages_count
 * @property-read array<\Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\DuesTransaction> $paidDues
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
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\TravelAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\OAuth2Client> $clients
 * @property-read int|null $clients_count
 * @property-read bool $is_student
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\OAuth2AccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static Builder|User accessActive()
 * @method static Builder|User accessInactive()
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
 * @method static Builder|User whereApiToken($value)
 * @method static Builder|User whereAutodeskEmail($value)
 * @method static Builder|User whereAutodeskInvitePending($value)
 * @method static Builder|User whereBuzzcardAccessOptOut($value)
 * @method static Builder|User whereClickupEmail($value)
 * @method static Builder|User whereClickupId($value)
 * @method static Builder|User whereClickupInvitePending($value)
 * @method static Builder|User whereCreateReason($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmergencyContactName($value)
 * @method static Builder|User whereEmergencyContactPhone($value)
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
 * @method static Builder|User whereMiddleName($value)
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
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class User extends Authenticatable
{
    use Actionable;
    use HasBelongsToManyEvents;
    use HasFactory;
    use HasRelationshipObservables;
    use HasRoles;
    use HasPermissions;
    use Notifiable;
    use SoftDeletes;
    use HasApiTokens;
    use FirstNameSynonyms;
    use Searchable;
    use Impersonatable;

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
        'autodesk_invite_pending' => 'boolean',
        'exists_in_sums' => 'boolean',
        'has_ever_logged_in' => 'boolean',
        'is_service_account' => 'boolean',
        'buzzcard_access_opt_out' => 'boolean',
    ];

    /**
     * The attributes that should be searchable in Meilisearch.
     *
     * @var array<string>
     */
    public $searchable_attributes = [
        'first_name',
        'preferred_name',
        'last_name',
        'uid',
        'gt_email',
        'gmail_address',
        'clickup_email',
        'autodesk_email',
        'github_username',
        'gtid',
        'phone',
        'gtDirGUID',
    ];

    /**
     * The rules to use for ranking results in Meilisearch.
     *
     * @var array<string>
     */
    public $ranking_rules = [
        'revenue_total:desc',
        'attendance_count:desc',
        'signatures_count:desc',
        'gtid:desc',
    ];

    /**
     * The attributes that can be used for filtering in Meilisearch.
     *
     * @var array<string>
     */
    public $filterable_attributes = [
        'class_standing_id',
        'major_id',
        'team_id',
        'permission_id',
        'role_id',
    ];

    /**
     * The attributes that Nova might think can be used for filtering, but actually can't.
     *
     * @var array<string>
     */
    public $do_not_filter_on = [
        'dues_package_id',
        'travel_id',
        'merchandise_id',
        'user_id',
    ];

    /**
     * List of valid shirt sizes and display names for them.
     *
     * @var array<string,string>
     *
     * @phan-suppress PhanReadOnlyPublicProperty
     */
    public static $shirt_sizes = [
        's' => 'Small',
        'm' => 'Medium',
        'l' => 'Large',
        'xl' => 'Extra-Large',
        'xxl' => 'XXL',
        'xxxl' => 'XXXL',
    ];

    protected string $guard_name = 'web';

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
        return implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]));
    }

    /**
     * Get whether this user is a current student.
     */
    public function getIsStudentAttribute(): bool
    {
        return 'student' === $this->primary_affiliation
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
        return $this->belongsToMany(
            ClassStanding::class
        )->whereNull(
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
        })->orWhere('access_override_until', '>=', date('Y-m-d H:i:s'));
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
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
     * @param  array<string>  $accountEntitlements
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
     * @param  array<string>  $accountEntitlements
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

    /**
     * Get the Signatures for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Signature>
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function hasSignedLatestAgreement(): bool
    {
        return $this
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
            )
            ->exists();
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

    public function getHomeDepartmentAttribute(): ?string
    {
        $uid = $this->uid;

        return Cache::remember('home_department_'.$uid, now()->addDay(), static function () use ($uid): ?string {
            $parentSpan = SentrySdk::getCurrentHub()->getSpan();

            if (null !== $parentSpan) {
                $context = new SpanContext();
                $context->setOp('ldap.get_home_department');
                $span = $parentSpan->startChild($context);
                SentrySdk::getCurrentHub()->setSpan($span);
            }

            $result = Adldap::search()
                ->where('uid', '=', $uid)
                ->where('employeeType', '=', 'employee')
                ->select('ou')
                ->get()
                ->pluck('ou')
                ->toArray();

            if (null !== $parentSpan) {
                // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
                $span->finish();
                SentrySdk::getCurrentHub()->setSpan($parentSpan);
            }

            return [] === $result ? null : $result[0][0];
        });
    }

    /**
     * Determine if the user can impersonate another user.
     */
    public function canImpersonate(): bool
    {
        return $this->can('impersonate-users');
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withCount('attendance')->withCount('signatures');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $array['revenue_total'] = intval(Payment::selectRaw(  // @phpstan-ignore-line
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
}
