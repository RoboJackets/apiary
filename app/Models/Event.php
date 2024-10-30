<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GetMorphClassStatic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * Represents a one-off gathering where an RSVP may be requested or attendance may be taken.
 *
 * @property int $id
 * @property string $name
 * @property bool $allow_anonymous_rsvp
 * @property int $organizer_id user_id of the organizer
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection|array<\App\Models\RemoteAttendanceLink> $remoteAttendanceLinks
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
 * @property-read int|null $attendance_count
 * @property-read string $organizer_name
 * @property-read \App\Models\User $organizer
 * @property-read int|null $remote_attendance_links_count
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Rsvp> $rsvps
 * @property-read int|null $rsvps_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Query\Builder|Event onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAllowAnonymousRsvp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereOrganizerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Event withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Event withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
 */
class Event extends Model
{
    use GetMorphClassStatic;
    use Searchable;
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'allow_anonymous_rsvp' => 'boolean',
        ];
    }

    public const RELATIONSHIP_PERMISSIONS = [
        'organizer' => 'read-users',
        'rsvps' => 'read-rsvps',
        'attendance' => 'read-attendance',
    ];

    /**
     * Get the organizer for this event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Event>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Get the Rsvps for this event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Rsvp, self>
     */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    /**
     * Get all of the event's attendance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Attendance, self>
     */
    public function attendance(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Get all of the event's remote attendance links.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\RemoteAttendanceLink, self>
     */
    public function remoteAttendanceLinks(): MorphMany
    {
        return $this->morphMany(RemoteAttendanceLink::class, 'attendable');
    }

    /**
     * Get the organizer_name attribute for the model.
     */
    public function getOrganizerNameAttribute(): string
    {
        return $this->organizer->name;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $array['start_time_unix'] = $this->start_time?->getTimestamp();
        $array['end_time_unix'] = $this->end_time?->getTimestamp();

        return $array;
    }
}
