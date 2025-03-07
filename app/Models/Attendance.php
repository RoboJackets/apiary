<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference

namespace App\Models;

use App\Observers\AttendanceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

/**
 * Represents a single attendance entry.
 *
 * @property int $id
 * @property string $attendable_type
 * @property int $attendable_id
 * @property int|null $gtid
 * @property string|null $source
 * @property int|null $recorded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $remote_attendance_link_id
 * @property int|null $people_counter_id
 * @property-read \App\Models\Event|\App\Models\Team $attendable
 * @property-read \App\Models\User|null $attendee
 * @property-read \App\Models\User|null $recorded
 * @property-read \App\Models\RemoteAttendanceLink|null $remoteAttendanceLink
 *
 * @method static Builder|Attendance end(string $date)
 * @method static Builder|Attendance newModelQuery()
 * @method static Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Query\Builder|Attendance onlyTrashed()
 * @method static Builder|Attendance query()
 * @method static Builder|Attendance start(string $date)
 * @method static Builder|Attendance whereAttendableId($value)
 * @method static Builder|Attendance whereAttendableType($value)
 * @method static Builder|Attendance whereCreatedAt($value)
 * @method static Builder|Attendance whereDeletedAt($value)
 * @method static Builder|Attendance whereGtid($value)
 * @method static Builder|Attendance whereId($value)
 * @method static Builder|Attendance wherePeopleCounterId($value)
 * @method static Builder|Attendance whereRecordedBy($value)
 * @method static Builder|Attendance whereRemoteAttendanceLinkId($value)
 * @method static Builder|Attendance whereSource($value)
 * @method static Builder|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Attendance withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Attendance withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
 */
#[ObservedBy([AttendanceObserver::class])]
class Attendance extends Model
{
    use Searchable;
    use SoftDeletes;

    /**
     * The name of the database table for this model.
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
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['attendable'];

    public const RELATIONSHIP_PERMISSIONS = [
        'attendee' => 'read-users',
        'recorded' => 'read-users',
    ];

    /**
     * Get all the owning attendable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\App\Models\Team|\App\Models\Event,\App\Models\Attendance>
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Attendance model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Attendance>
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gtid', 'gtid');
    }

    /**
     * Get the access card associated with the Attendance model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AccessCard, \App\Models\Attendance>
     */
    public function accessCard(): BelongsTo
    {
        return $this->belongsTo(AccessCard::class, 'access_card_number', 'access_card_number');
    }

    /**
     * Get the User who recorded the Attendance model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Attendance>
     */
    public function recorded(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the RemoteAttendanceLink that created the Attendance model.
     *
     * @return BelongsTo<RemoteAttendanceLink, Attendance>
     */
    public function remoteAttendanceLink(): BelongsTo
    {
        return $this->belongsTo(RemoteAttendanceLink::class);
    }

    /**
     * Scope query to start at given date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>
     */
    public function scopeStart(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    /**
     * Scope query to end at given date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>
     */
    public function scopeEnd(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    /**
     * Transform an array of Attendance objects into a CSV file.
     *
     * @param  iterable<\App\Models\Attendance>  $attendance
     */
    public static function formatAsCsv(iterable $attendance): string
    {
        $attendables = [];

        // Iterate over each GTID, transforming it into an array of attendables to counts, then ensure every row has
        // all columns

        // Transform the attendance records into [gtid => [attendable => count, ...], ...]
        $collection = collect($attendance)->groupBy('gtid')
            ->map(static function (Collection $records) use (&$attendables): Collection {
                // Group the attendance records for that GTID by the attendable
                return $records->groupBy(static function (self $item) use (&$attendables): string {
                    $name = $item->attendable->name;
                    $attendables[] = $name;

                    return $name;
                })->map(static fn (Collection $days): int => $days->count());
            });

        // Get an array of all possible attendables with the value of 0 for each
        $attendables = collect($attendables)->unique()
            ->mapWithKeys(static fn (string $attendable): array => [$attendable => 0]);

        // Ensure each value in the previous arrary has all possible attendable keys, then prepend the GTID:
        // [gtid => ['GTID' => gtid, attendable => count, attendable => count, ...], ...]
        $collection = $collection->map(
            static fn (Collection $columns, int $gtid): Collection => $columns->union(
                $attendables
            )->sortKeys()->prepend(
                $gtid,
                'GTID'
            )
        );

        $attendables_array = $attendables->sortKeys()->keys()->all();

        $csv = 'Name,Email,Major,'.implode(',', $attendables_array)."\n";

        foreach ($collection as $person) {
            $user = User::where('gtid', $person->get('GTID'))->first();
            if ($user === null) {
                // Skip when there's not a user because it's likely to not be a valid GTID.
                continue;
            }
            $majors = $user->majors->pluck('whitepages_ou')->join('/');
            $row = $user->full_name.','.$user->gt_email.','.$majors;
            foreach ($attendables_array as $attendable) {
                $row .= ','.$person->get($attendable);
            }
            $csv .= $row."\n";
        }

        return $csv;
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance> $query
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        // Not indexing recorded or remoteAttendanceLink relationships, I don't think those are useful

        return $query->with('attendable')->with('attendee');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string|null>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        if (! array_key_exists('attendable', $array)) {
            $array['attendable'] = $this->attendable->toArray();
        }

        if (! array_key_exists('attendee', $array) && $this->attendee !== null) {
            $array['attendee'] = $this->attendee->toArray();
        }

        if ($this->attendable_type === Team::getMorphClassStatic()) {
            $array['team_id'] = $this->attendable_id;
            $array['event_id'] = null;
        } elseif ($this->attendable_type === Event::getMorphClassStatic()) {
            $array['team_id'] = null;
            $array['event_id'] = $this->attendable_id;
        }

        $array['user_id'] = $this->attendee?->id;

        unset($array['attendable']['organizer']);
        unset($array['attendable']['organizer_name']);

        $array['updated_at_unix'] = $this->updated_at?->getTimestamp();

        return $array;
    }
}
