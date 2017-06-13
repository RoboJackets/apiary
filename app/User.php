<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
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
}
