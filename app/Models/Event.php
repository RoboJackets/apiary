<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a one-off gathering where an RSVP may be requested or attendance may be taken.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAllowAnonymousRsvp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereOrganizerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Event onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Event withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|Event withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property bool $allow_anonymous_rsvp Whether anonymous RSVPs are allowed for this event
 * @property float $cost
 * @property float $price The cost to attend this event
 * @property int $id The database ID for this Event
 * @property int $organizer_id
 * @property string $name The name of the event
 * @property string|null $location
 *
 * @property-read \App\Models\User $organizer
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Rsvp> $rsvps
 * @property-read int|null $attendance_count
 * @property-read int|null $rsvps_count
 * @property-read string $organizer_name
 */
class Event extends Model
{
    use GetMorphClassStatic;
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
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'allow_anonymous_rsvp' => 'boolean',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    /**
     * Get all of the event's attendance.
     */
    public function attendance(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Get all of the event's remote attendance links.
     */
    public function remoteAttendanceLinks(): MorphMany
    {
        return $this->morphMany(RemoteAttendanceLink::class, 'attendable');
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
