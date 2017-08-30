<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rsvp extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function event()
    {
        return $this->hasOne('App\Event');
    }
}
