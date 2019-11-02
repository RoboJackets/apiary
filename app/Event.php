<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
