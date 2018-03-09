<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

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

    /**
     * Scope a query to only include attendable teams
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAttendable($query)
    {
        return $query->where('attendable', true);
    }

    /**
     * Scope a query to only include visible (non-hidden) teams
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('hidden', false);
    }
}
