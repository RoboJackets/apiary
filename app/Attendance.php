<?php

declare(strict_types=1);

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Represents a single attendance entry.
 *
 * @property int $id The database identifier for this entry
 * @property int $gtid the GTID associated with this entry
 */
class Attendance extends Model
{
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
     */
    public function scopeStart(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    /**
     * Scope query to end at given date.
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

    /**
     * Transform an array of Attendance objects into a CSV file.
     *
     * @param array<\App\Attendance> $attendance
     */
    public static function formatAsCSV(iterable $attendance): string
    {
        $attendables = [];

        // Iterate over each GTID, transforming it into an array of attendables to counts, then ensure every row has
        // all columns

        // Transform the attendance records into [gtid => [attendable => count, ...], ...]
        $collection = $attendance->groupBy('gtid')
            ->map(static function (Collection $records) use (&$attendables): Collection {
                // Group the attendance records for that GTID by the attendable
                return $records->groupBy(static function (Attendance $item) use (&$attendables): string {
                    $name = $item->attendable->name;
                    $attendables[] = $name;

                    return $name;
                })->map(static function (Collection $days): int {
                    return $days->count();
                });
            });

        // Get an array of all possible attendables with the value of 0 for each
        $attendables = collect($attendables)->unique()
            ->mapWithKeys(static function (string $attendable): array {
                return [$attendable => 0];
            });

        // Ensure each value in the previous arrary has all possible attendable keys, then prepend the GTID:
        // [gtid => ['GTID' => gtid, attendable => count, attendable => count, ...], ...]
        $collection = $collection->map(static function (Collection $columns, int $gtid) use ($attendables): Collection {
            return $columns->union($attendables)->sortKeys()->prepend($gtid, 'GTID');
        });

        $attendables_array = $attendables->sortKeys()->keys()->all();

        $csv = 'Name,Email,Major,'.implode(',', $attendables_array).'\n';

        foreach ($collection as $person) {
            $user = User::where('gtid', $person->get('GTID'))->first();
            if (null === $user) {
                // Skip when there's not a user because it's likely to not be a valid GTID.
                continue;
            }
            $majors = $user->majors->pluck('whitepages_ou')->join('/');
            $row = $user->full_name.','.$user->gt_email.','.$majors;
            foreach ($attendables_array as $attendable) {
                $row .= ','.$person->get($attendable);
            }
            $csv .= $row.'\n';
        }

        return $csv;
    }
}
