<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Model implements Authenticatable
{
    use SoftDeletes;

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
