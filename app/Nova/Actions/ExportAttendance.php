<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference

namespace App\Nova\Actions;

use App\Attendance;
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
        $hash = hash('sha256', random_bytes(64));

        $file = 'attendance-reports/'.$hash.'.csv';

        Storage::append($file, Attendance::formatAsCSV($models));

        return Action::download(
            route('api.v1.attendancereport.show', ['hash' => $hash]),
            'RoboJacketsAttendance.csv'
        );
    }
}
