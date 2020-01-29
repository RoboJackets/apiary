<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a one-off gathering where an RSVP may be requested or attendance may be taken.
 *
 * @property bool $allow_anonymous_rsvp Whether anonymous RSVPs are allowed for this event
 * @property int $id The database ID for this Event
 * @property float $price The cost to attend this event
 * @property string $name The name of the event
 */
class Event extends Model
{
    use SoftDeletes;

    /**
     * Attributes to mutate to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'start_time',
        'end_time',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'organizer_name',
        'organizer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'organizer_name',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'allow_anonymous_rsvp' => 'boolean',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'organizer_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(\App\Rsvp::class);
    }

    /**
     * Get all of the event's attendance.
     */
    public function attendance(): MorphMany
    {
        return $this->morphMany(\App\Attendance::class, 'attendable');
    }

    /**
     * Get the Payable amount.
     */
    public function getPayableAmount(): float
    {
        return $this->price;
    }

    /**
     * Get the organizer_name attribute for the model.
     */
    public function getOrganizerNameAttribute(): string
    {
        return $this->organizer->name;
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string, string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'organizer' => 'users',
            'rsvps' => 'rsvps',
            'attendance' => 'attendance',
        ];
    }
}
