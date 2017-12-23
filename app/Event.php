<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;
    
    protected $dates = [
        'created_at',
        'updated_at',
        'start_time',
        'end_time'
    ];
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function organizer()
    {
        return $this->belongsTo('App\User', 'organizer');
    }

    public function rsvps()
    {
        return $this->hasMany('App\Rsvp');
    }

    /**
     * Get all of the event's attendance.
     */
    public function attendance()
    {
        return $this->morphMany('App\Attendance', 'attendable');
    }

    /**
     * Get the Payable amount
     */
    public function getPayableAmount()
    {
        return ($this->price) ?: null;
    }
}
