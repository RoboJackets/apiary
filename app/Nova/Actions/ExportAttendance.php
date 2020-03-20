<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference

namespace App\Nova\Actions;

use App\Attendance;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ExportAttendance extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export for CoC';

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * The number of models that should be included in each chunk.
     *
     * @var int
     */
    public static $chunkCount = 100000;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Attendance>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $attendables = [];

        // Iterate over each GTID, transforming it into an array of attendables to counts, then ensure every row has
        // all columns

        // Transform the attendance records into [gtid => [attendable => count, ...], ...]
        $collection = $models->groupBy('gtid')
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

        $hash = hash('sha256', random_bytes(64));

        $file = 'attendance-reports/'.$hash.'.csv';

        $attendables_array = $attendables->sortKeys()->keys()->all();

        Storage::append($file, 'Name,Email,Major,'.implode(',', $attendables_array));

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
            Storage::append($file, $row);
        }

        return Action::download(
            route('api.v1.attendancereport.show', ['hash' => $hash]),
            'RoboJacketsAttendance.csv'
        );
    }
}
