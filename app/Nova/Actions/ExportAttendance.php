<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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
     * @param  \Illuminate\Support\Collection<int,\App\Models\Attendance>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $hash = hash('sha256', random_bytes(64));

        $file = 'nova-exports/'.$hash.'.csv';

        // Redundant collect() to make Phan happy
        Storage::append($file, Attendance::formatAsCsv(collect($models)));

        // Generate signed URL to pass to backend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $hash.'.csv'], now()->addMinutes(5));

        return Action::downloadURL($url, 'RoboJacketsAttendance.csv');
    }
}
