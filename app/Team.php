<?php

namespace App;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Laravel\Nova\Actions\Actionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Chelout\RelationshipEvents\Concerns\HasManyEvents;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Traits\HasRelationshipObservables;

class Team extends Model
{
    use Actionable, SoftDeletes, HasSlug, HasManyEvents, HasBelongsToManyEvents, HasRelationshipObservables;

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
        return $this->belongsToMany(\App\User::class)->withTimestamps();
    }

    /**
     * Get all of the team's attendance.
     */
    public function attendance()
    {
        return $this->morphMany(\App\Attendance::class, 'attendable');
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
     * Scope a query to only include self-serviceable teams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelfServiceable($query)
    {
        return $query->where('self_serviceable', true);
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

    /**
     * Map of relationships to permissions for dynamic inclusion.
     * @return array
     */
    public function getRelationshipPermissionMap()
    {
        return [
            'members' => 'teams-membership',
            'attendance' => 'attendance',
        ];
    }

    public function projectManager()
    {
        return $this->belongsTo(\App\User::class, 'project_manager_id');
    }
}
