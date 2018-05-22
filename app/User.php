<?php

namespace App;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;
    use HasRoles;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'full_name',
        'preferred_first_name',
        'is_active', ];

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
        'needs_payment',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['api_token'];

    /**
     *  Get the FASET visits associated with this user.
     */
    public function fasetVisits()
    {
        return $this->hasMany('App\FasetVisit');
    }

    /**
     *  Get the Teams that this User is a member of.
     */
    public function teams()
    {
        return $this->belongsToMany('App\Team');
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
        return $this->hasMany('App\DuesTransaction');
    }

    /**
     * Get the events organized by the User.
     */
    public function organizes()
    {
        return $this->hasMany('App\Event', 'organizer');
    }

    /**
     * Get the RSVPs belonging to the User.
     */
    public function rsvps()
    {
        return $this->hasMany('App\Rsvp');
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
        return 'uid';
    }

    public function getAuthIdentifier()
    {
        return $this->uid;
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
     * Get the is_active flag for the User.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        if ($this->dues->count() > 0) {
            $lastDuesTransaction = $this->dues->last();
            $pkgIsActive = $lastDuesTransaction->package->is_active;
            $hasPayment = ($lastDuesTransaction->payment()->exists());
            if ($hasPayment) {
                $paidTotal = ($lastDuesTransaction->payment->sum('amount') >= $lastDuesTransaction->getPayableAmount());

                return $paidTotal && $pkgIsActive;
            } else {
                return false;
            }
        } else {
            return false;
        }
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
}
