<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     *  Get the Users that are members of this Team
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    /**
     * Get all of the team's attendance.
     */
    public function attendance()
    {
        return $this->morphMany('App\Attendance', 'attendable');
    }
}
