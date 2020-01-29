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
use Laravel\Nova\Actions\Actionable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Represents a user, possibly a member and possibly not.
 *
 * @method static \Illuminate\Database\Eloquent\Builder accessActive() scopes to only users that are access active
 * @method static \Illuminate\Database\Eloquent\Builder active() scopes to only users that are active
 * @method static \Illuminate\Database\Eloquent\Builder findByIdentifier(string $id) finds a user by any identifier
 * @method static \Illuminate\Database\Eloquent\Builder hasOverride() scopes to only users with an active override
 *
 * @property bool $is_active whether the user is currently active
 * @property string $name the display name for this user
 */
class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;
    use HasRoles;
    use Actionable;
    use HasBelongsToManyEvents;
    use HasRelationshipObservables;

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
    public function getPreferredFirstNameAttribute(): string
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

    /**
     * Route notifications for the mail channel.
     * Send to GT email when present and fall back to personal email if not.
     */
    public function routeNotificationForMail(): string
    {
        return $this->gt_email ?? $this->personal_email;
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

        if (! is_numeric($id)) {
            return $query->where('uid', $id);
        }

        return $query;
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
}
