<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Model implements Authenticatable
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'name', 'full_name', 'id', 'deleted_at', 'created_at', 'updated_at'
    ];

    /**
     *  Get the FASET visits associated with this user
     */
    public function fasetVisits()
    {
        return $this->hasMany('App\FasetVisit');
    }

    /**
     *  Get the Teams that this User is a member of
     */
    public function teams()
    {
        return $this->belongsToMany('App\Team');
    }

    /**
     * Route notifications for the mail channel.
     * Send to GT email when present and fall back to personal email if not
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return (isset($this->gt_email)) ? $this->gt_email : $this->personal_email;
    }

    public function organizes()
    {
        return $this->belongsToMany('App\Event');
    }

    public function rsvps()
    {
        return $this->hasMany('App\Rsvp');
    }

    public function getAuthIdentifierName()
    {
        return "uid";
    }

    public function getAuthIdentifier()
    {
        return $this->uid;
    }

    public function getAuthPassword()
    {
        throw new \BadMethodCallException("Not implemented");
    }

    public function getRememberToken()
    {
        throw new \BadMethodCallException("Not implemented");
    }

    public function setRememberToken($value)
    {
        throw new \BadMethodCallException("Not implemented");
    }

    public function getRememberTokenName()
    {
        throw new \BadMethodCallException("Not implemented");
    }
}
