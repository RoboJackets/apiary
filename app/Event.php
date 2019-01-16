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
        'end_time',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at', 'organizer_name', 'organizer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'organizer_name',
    ];

    public function organizer()
    {
        return $this->belongsTo(\App\User::class, 'organizer_id');
    }

    public function rsvps()
    {
        return $this->hasMany(\App\Rsvp::class);
    }

    /**
     * Get all of the event's attendance.
     */
    public function attendance()
    {
        return $this->morphMany(\App\Attendance::class, 'attendable');
    }

    /**
     * Get the Payable amount.
     */
    public function getPayableAmount()
    {
        return ($this->price) ?: null;
    }

    /**
     * Get the organizer_name attribute for the model.
     */
    public function getOrganizerNameAttribute()
    {
        return $this->organizer->name;
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     * @return array
     */
    public function getRelationshipPermissionMap()
    {
        return [
            'organizer' => 'users',
            'rsvps' => 'rsvps',
            'attendance' => 'attendance',
        ];
    }
}
