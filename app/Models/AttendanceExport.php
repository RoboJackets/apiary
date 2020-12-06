<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents an attendance report export link.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereDownloadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereDownloadUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceExport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|AttendanceExport onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|AttendanceExport withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|AttendanceExport withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|\Carbon\Carbon $end_time
 * @property \Illuminate\Support\Carbon|\Carbon\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|\Carbon\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $downloaded_at
 * @property int $id
 * @property int|null $download_user_id
 * @property string $secret the secret in the URL
 *
 * @property-read \App\Models\User $downloadUser
 */
class AttendanceExport extends Model
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
        'start_time',
        'end_time',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'start_time',
        'end_time',
        'expires_at',
        'downloaded_at',
    ];

    public function downloadUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
