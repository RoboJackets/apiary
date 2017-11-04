<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;
    
    //Override automatic table name generation
    protected $table = 'attendance';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
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
