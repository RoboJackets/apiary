<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a remote attendance link.
 */
class RemoteAttendanceLink extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'secret',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['attendable'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get all of the owning attendable models.
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
