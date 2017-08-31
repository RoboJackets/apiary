<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

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
        return $this->belongsTo('App\User');
    }

    public function rsvps()
    {
        return $this->hasMany('App\Rsvp');
    }
}
