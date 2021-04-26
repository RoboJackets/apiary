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
