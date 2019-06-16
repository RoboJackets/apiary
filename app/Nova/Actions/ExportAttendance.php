<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * The exported filename
     *
     * @var string
     */
    protected $filename = 'RoboJacketsAttendance.csv';

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $attendables = [];

        // Iterate over each GTID, transforming it into an array of attendables to counts, then ensure every row has
        // all columns
        $collection = $models->groupBy('gtid')
            ->map(static function ($records) use (&$attendables) {
                // Group the attendance records for that GTID by the attendable
                return $records->groupBy(static function ($item) use (&$attendables) {
                    $name = $item->attendable->name;
                    $attendables[] = $name;
                    return $name;
                })->map(static function ($days) {
                    return $days->count();
                });
            });

        // Get an array of all possible attendables with the value of 0 for each
        $attendables = collect($attendables)->unique()
            ->mapWithKeys(static function ($attendable) {
                return [$attendable => 0];
            });

        $collection = $collection->map(static function ($columns, $gtid) use ($attendables) {
            return $columns->union($attendables)->prepend($gtid, 'GTID');
        });

        $response = $collection->downloadExcel($this->filename, \Maatwebsite\Excel\Excel::CSV, true);
        if (!$response instanceof BinaryFileResponse || $response->isInvalid()) {
            return Action::danger('Error exporting attendance');
        }

        $downloadURL = url('/nova-vendor/maatwebsite/laravel-nova-excel/download?') . http_build_query([
            'path' => $response->getFile()->getPathname(),
            'filename' => $this->filename,
        ]);
        return Action::download($downloadURL, $this->filename);
    }
}
