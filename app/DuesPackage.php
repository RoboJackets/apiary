<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DuesPackage extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the DuesTransaction associated with the DuesPackage model.
     */
    public function transactions()
    {
        return $this->hasMany('App\DuesTransaction');
    }

    /**
     * Scope a query to only include active DuesPackages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
