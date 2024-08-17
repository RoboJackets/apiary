<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
 * @property-read \App\Models\Team|\App\Models\Event $attendable
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
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
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
     * @var array<int,string>
     */
    protected $with = ['attendable'];

    /**
     * A regular expression for acceptable redirect URLs for normal users to enter.
     * The regex will match any of the following, with the https, http, or no schema, and optional query strings:
     * https://meet.google.com/<alpha and dashes>
     * https://teams.microsoft.com/l/meetup-join/<alphanumeric, -, %, .>/<digits>
     * https://gatech.zoom.us/j/<digits>
     * but nothing else, to avoid users redirecting to surprising things.
     *
     * @phan-suppress PhanReadOnlyPublicProperty
     */
    public static string $redirectRegex = '/^(https?:\/\/)?(meet\.google\.com\/[-a-z]+|teams\.microsoft\.com\/l\/'
        .'meetup-join\/[-a-zA-Z0-9%\._]+\/[0-9]+|gatech\.zoom\.us\/j\/[0-9]+)(\?[^@]*)?$/';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Given a user-submitted URL matching $redirectRegex, return a normalized URL that can be used for redirects.
     */
    public static function normalizeRedirectUrl(string $url): string
    {
        $url = Str::lower($url);

        if (Str::startsWith($url, 'https://')) {
            return $url;
        }

        if (Str::startsWith($url, 'http://')) {
            return Str::replaceFirst($url, 'http://', 'https://');
        }

        return 'https://'.$url;
    }

    /**
     * Get all of the owning attendable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\App\Models\Event|\App\Models\Team,\App\Models\RemoteAttendanceLink>
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the associated attendance records.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Attendance>
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
