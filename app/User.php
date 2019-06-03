<?php declare(strict_types = 1);

namespace App;

use Laravel\Nova\Actions\Actionable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Traits\HasRelationshipObservables;
use Illuminate\Database\Eloquent\Builder;

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
     * @var array
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
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'accept_safety_agreement',
        'access_override_until',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
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
     * @var array
     */
    protected $hidden = ['api_token', 'gender', 'ethnicity', 'dues'];

    /**
     *  Get the recruiting visits associated with this user.
     */
    public function recruitingVisits()
    {
        return $this->hasMany(\App\RecruitingVisit::class);
    }

    /**
     *  Get the attendance records associated with this user.
     */
    public function attendance()
    {
        return $this->hasMany(\App\Attendance::class, 'gtid', 'gtid');
    }

    /**
     *  Get the teams that this user manages.
     */
    public function manages()
    {
        return $this->hasMany(\App\Team::class, 'project_manager_id');
    }

    /**
     *  Get the Teams that this User is a member of.
     */
    public function teams()
    {
        return $this->belongsToMany(\App\Team::class)->withTimestamps();
    }

    /**
     * Check membership status for a given team.
     *
     * @param  $team Team ID
     *
     * @return bool Whether or not user is a member of the given team
     */
    public function memberOfTeam($team): bool
    {
        return $this->teams->contains($team);
    }

    /**
     * Get the name associated with the User.
     */
    public function getNameAttribute()
    {
        $first = $this->preferred_name ?: $this->first_name;

        return implode(' ', [$first, $this->last_name]);
    }

    /**
     * Get the preferred first name associated with the User.
     */
    public function getPreferredFirstNameAttribute()
    {
        return $this->preferred_name ?: $this->first_name;
    }

    /**
     * Set the preferred first name associated with the User. Stores null if preferred name matches legal name.
     */
    public function setPreferredFirstNameAttribute($preferred_name): void
    {
        $this->attributes['preferred_name'] = $preferred_name === $this->first_name ? null : $preferred_name;
    }

    /**
     * Get the full name associated with the User.
     */
    public function getFullNameAttribute()
    {
        return implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]));
    }

    /*
     * Get the DuesTransactions belonging to the User
     */
    public function dues()
    {
        return $this->hasMany(\App\DuesTransaction::class);
    }

    /*
     * Get the DuesTransactions belonging to the User
     */
    public function duesTransactions()
    {
        return $this->hasMany(\App\DuesTransaction::class);
    }

    /*
     * Get the DuesTransactions belonging to the User
     */
    public function paidDues()
    {
        return $this->hasMany(\App\DuesTransaction::class)->paid();
    }

    /**
     * Get the events organized by the User.
     */
    public function events()
    {
        return $this->hasMany(\App\Event::class, 'organizer_id');
    }

    /**
     * Get the RSVPs belonging to the User.
     */
    public function rsvps()
    {
        return $this->hasMany(\App\Rsvp::class);
    }

    /**
     * Route notifications for the mail channel.
     * Send to GT email when present and fall back to personal email if not.
     *
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        return $this->gt_email ?? $this->personal_email;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword(): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getRememberToken(): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function setRememberToken($value): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getRememberTokenName(): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array
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
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return 0 !== self::where('id', $this->id)->active()->count();
    }

    /**
     * Get the access_active flag for the User.
     *
     * @return bool
     */
    public function getIsAccessActiveAttribute(): bool
    {
        return 0 !== self::where('id', $this->id)->accessActive()->count();
    }

    /**
     * Scope a query to automatically determine user identifier.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindByIdentifier(Builder $query, string $id): Builder
    {
        if (is_numeric($id) && 9 === strlen($id) && 9 === $id[0]) {
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('dues', static function ($q): void {
            $q->paid()->current();
        });
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereDoesntHave('dues', static function ($q): void {
            $q->paid()->current();
        });
    }

    /**
     * Scope a query to automatically include only access active members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessActive(Builder $query): Builder
    {
        return $query->whereHas('dues', static function ($q): void {
            $q->paid()->accessCurrent();
        })->orwhere('access_override_until', '>=', date('Y-m-d H:i:s'));
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessInactive(Builder $query): Builder
    {
        return $query->whereDoesntHave('dues', static function ($q): void {
            $q->paid()->accessCurrent();
        })->where(static function ($query): void {
            $query->where('access_override_until', '<=', date('Y-m-d H:i:s'))->orWhereNull('access_override_until');
        });
    }

    public function accessOverrideBy()
    {
        return $this->belongsTo(self::class, 'access_override_by_id');
    }
}
