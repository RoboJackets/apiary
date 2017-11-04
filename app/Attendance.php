<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /**
     * Get all of the owning attendable models.
     */
    public function attendable()
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Attendance model.
     */
    public function attendee()
    {
        return $this->belongsTo('App\User', 'gtid', 'gtid');
    }
}
