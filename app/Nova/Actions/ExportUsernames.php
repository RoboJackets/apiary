<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ExportUsernames extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export Usernames';

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * The number of models that should be included in each chunk.
     *
     * @var int
     */
    public static $chunkCount = 100000;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     *
     * @phan-suppress PhanTypeMismatchArgument
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $output = $models->pluck('uid')->reduce(
            static fn (?string $carry, string $username): string => ($carry ?? '').$username."\n"
        );

        $timestamp = Carbon::now()->toDateTimeLocalString();
        $filename = 'usernames-'.$timestamp.'.csv';
        $path = 'nova-exports/'.$filename;

        Storage::put($path, $output);

        // Generate signed URL to pass to backend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return Action::downloadURL($url, $filename);
    }
}
