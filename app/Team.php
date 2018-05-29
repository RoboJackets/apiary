<?php

namespace App;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes, HasSlug;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     *  Get the Users that are members of this Team.
     */
    public function members()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }

    /**
     * Get all of the team's attendance.
     */
    public function attendance()
    {
        return $this->morphMany('App\Attendance', 'attendable');
    }

    /**
     * Scope a query to only include attendable teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAttendable($query)
    {
        return $query->where('attendable', true);
    }

    /**
     * Scope a query to only include visible teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['name'])
            ->saveSlugsTo('slug');
    }
}
