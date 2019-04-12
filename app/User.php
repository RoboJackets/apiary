<?php

namespace App;

use Laravel\Nova\Actions\Actionable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Traits\HasRelationshipObservables;

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
     * @param $team Team ID
     * @return bool Whether or not user is a member of the given team
     */
    public function memberOfTeam($team)
    {
        return $this->teams->contains($team);
    }

    /**
     * Get the name associated with the User.
     */
    public function getNameAttribute()
    {
        $first = ($this->preferred_name) ?: $this->first_name;

        return implode(' ', [$first, $this->last_name]);
    }

    /**
     * Get the preferred first name associated with the User.
     */
    public function getPreferredFirstNameAttribute()
    {
        return ($this->preferred_name) ?: $this->first_name;
    }

    /**
     * Set the preferred first name associated with the User. Stores null if preferred name matches legal name.
     */
    public function setPreferredFirstNameAttribute($preferred_name)
    {
        $this->attributes['preferred_name'] = $preferred_name == $this->first_name ? null : $preferred_name;
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
    public function routeNotificationForMail()
    {
        return (isset($this->gt_email)) ? $this->gt_email : $this->personal_email;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getRememberToken()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function setRememberToken($value)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getRememberTokenName()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     * @return array
     */
    public function getRelationshipPermissionMap()
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
    public function getIsActiveAttribute()
    {
        return self::where('id', $this->id)->active()->count() != 0;
    }

    /**
     * Get the access_active flag for the User.
     *
     * @return bool
     */
    public function getIsAccessActiveAttribute()
    {
        return self::where('id', $this->id)->accessActive()->count() != 0;
    }

    /**
     * Scope a query to automatically determine user identifier.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindByIdentifier($query, $id)
    {
        if (is_numeric($id) && strlen($id) == 9 && $id[0] == 9) {
            return $query->where('gtid', $id);
        } elseif (is_numeric($id)) {
            return $query->where('id', $id);
        } elseif (! is_numeric($id)) {
            return $query->where('uid', $id);
        } else {
            return $query;
        }
    }

    /**
     * Scope a query to automatically include only active members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereHas('dues', function ($q) {
            $q->paid()->current();
        });
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->whereDoesntHave('dues', function ($q) {
            $q->paid()->current();
        });
    }

    /**
     * Scope a query to automatically include only access active members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessActive($query)
    {
        return $query->whereHas('dues', function ($q) {
            $q->paid()->accessCurrent();
        })->orwhere('access_override_until', '>=', date('Y-m-d H:i:s'));
    }

    /**
     * Scope a query to automatically include only inactive members
     * Active: Has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessInactive($query)
    {
        return $query->whereDoesntHave('dues', function ($q) {
            $q->paid()->accessCurrent();
        })->where(function ($query) {
            $query->where('access_override_until', '<=', date('Y-m-d H:i:s'))
                ->orWhereNull('access_override_until');
        });
    }

    public function accessOverrideBy()
    {
        return $this->belongsTo(\App\User::class, 'access_override_by_id');
    }
}
