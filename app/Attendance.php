<?php declare(strict_types = 1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendance extends Model
{
    use SoftDeletes;

    /**
     * The name of the database table for this model
     *
     * @var string
     */
    protected $table = 'attendance';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * Get all of the owning attendable models.
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Attendance model.
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gtid', 'gtid');
    }

    /**
     * Get the User who recorded the Attendance model.
     */
    public function recorded(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope query to start at given date.
     *
     * @param Builder $query
     * @param string $date Date to start search
     *
     * @return Builder
     */
    public function scopeStart(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    /**
     * Scope query to end at given date.
     *
     * @param Builder $query
     * @param string $date Date to start search
     *
     * @return Builder
     */
    public function scopeEnd(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string, string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'attendee' => 'users',
            'recorded' => 'users',
        ];
    }
}
