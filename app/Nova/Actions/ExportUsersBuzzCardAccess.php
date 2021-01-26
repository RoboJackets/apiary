<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class ExportUsersBuzzCardAccess extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export BuzzCard Access List';

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $population = $fields->population;
        if ($population === 'core') {
            $users = User::select('gtid')->BuzzCardAccessEligible()
                ->whereHas('teams', function (Builder $query) {
                    $query->where('name', 'Core');
                })->get();
        } elseif ($population === 'general') {
            $users = User::select('gtid')->BuzzCardAccessEligible()
                ->whereDoesntHave('teams', function (Builder $query) {
                    $query->where('name', 'Core');
                })->get();
        } else {
            return Action::danger('Invalid population!');
        }

        if (count($users) == 0) {
            return Action::danger('No users match the provided criteria');
        }

        // Exclude fields that we don't care about (Everything except GTID)
        $users->forget("name","full_name","preferred_first_name","is_active","is_access_active");

        $filename = "robojackets-$population-buzzcard-" . time() . '.csv';
        $path = "nova-exports/$filename";
        $users->storeExcel(
            $path,
            'local',
            $writerType = null,
            $headings = false
        );

        // Generate signed URL to pass to backend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return Action::download($url, $filename);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Population')->options([
                'core' => 'Core',
                'general' => 'General'
            ]),
        ];
    }
}
