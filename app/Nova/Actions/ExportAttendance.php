<?php

namespace App\Nova\Actions;

use Laravel\Nova\Fields\Date;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
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

    protected $filename = 'RoboJacketsAttendance.csv';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // $collection = collect([['gtid'=>'903311111', 'today'=>'1'], ['gtid'=>'903311112', 'today'=>'2']]);
        $attendables = [];

        // Iterate over each GTID, transforming it into an array of attendables to counts, then ensure every row has all columns
        $collection = $models->groupBy('gtid')
            ->map(function ($records) use (&$attendables) {
                // Group the attendance records for that GTID by the attendable
                return $records->groupBy(function ($item) use (&$attendables) {
                    $name = $item->attendable->name;
                    $attendables[] = $name;
                    return $name;
                })->map(function ($days) {
                    return $days->count();
                });
            });

        \Log::debug('Before union', $collection->toArray());
        \Log::debug('Attendables before unique', $attendables);
        // Get an array of all possible attendables with the value of 0 for each
        $attendables = collect($attendables)->unique()
            ->mapWithKeys(function ($attendable) {
                return [$attendable => 0];
            });
        \Log::debug('Attendables after unique', $attendables->toArray());

        $collection = $collection->map(function ($columns, $gtid) use ($attendables) {
                return $columns->union($attendables)->prepend($gtid, 'GTID');
            });
        \Log::debug('After union', $collection->toArray());

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

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
