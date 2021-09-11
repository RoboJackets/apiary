<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a remote attendance link.
 *
 * @property int $id
 * @property string $secret
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string|null $redirect_url
 * @property string|null $note
 * @property string $attendable_type
 * @property int $attendable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Barryvdh\LaravelIdeHelper\Eloquent $attendable
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Attendance> $attendance
 * @property-read int|null $attendance_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink newQuery()
 * @method static \Illuminate\Database\Query\Builder|RemoteAttendanceLink onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereAttendableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereAttendableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemoteAttendanceLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|RemoteAttendanceLink withTrashed()
 * @method static \Illuminate\Database\Query\Builder|RemoteAttendanceLink withoutTrashed()
 * @mixin         \Barryvdh\LaravelIdeHelper\Eloquent
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
     * A regular expression for acceptable redirect URLs for normal users to enter.
     * The regex will match any of the following:
     * https://bluejeans.com/<digits, optional query string>
     * https://bluejeans.com/<digits>/<digits, optional query string>
     * https://gatech.bluejeans.com/<digits, optional query string>
     * https://gatech.bluejeans.com/<digits>/<digits, optional query string>
     * https://primetime.bluejeans.com/a2m/live-event/<alpha>
     * https://meet.google.com/<alpha and dashes>
     * https://teams.microsoft.com/l/meetup-join/<alphanumeric, -, %, .>/<digits, optional query string>
     * but nothing else, to avoid users redirecting to surprising things.
     *
     * @phan-suppress PhanReadOnlyPublicProperty
     */
    public static string $redirectRegex = '/^https:\/\/((gatech\.)?bluejeans\.com\/[0-9]+(\/[0-9]+)?|primetime\.'
        .'bluejeans\.com\/a2m\/live-event\/[a-z]+|meet\.google\.com\/[-a-z]+|teams\.microsoft\.com\/l\/'
        .'meetup-join\/[-a-zA-Z0-9%\._]+\/[0-9]+)(\?[^@]*)?$/';

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
