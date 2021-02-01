<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class ExportUsersBuzzCardAccess extends Action
{
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
     *
     * @return array|string[]
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $population = $fields->population;
        $query = User::query();

        $query->when('core' === $population, static function (Builder $q) {
            return $q->select('gtid')->BuzzCardAccessEligible()
                ->whereHas('teams', static function (Builder $query): void {
                    $query->where('name', 'Core');
                });
        });
        $query->when('general' === $population, static function (Builder $q) {
            return $q->select('gtid')->BuzzCardAccessEligible()
                ->whereDoesntHave('teams', static function (Builder $query): void {
                    $query->where('name', 'Core');
                });
        });

        $users = $query->get();

        if (0 === count($users)) {
            return Action::danger('No users match the provided criteria!');
        }

        // Exclude fields that we don't care about (Everything except GTID)
        $users->pluck('gtid');

        $filename = 'robojackets-'.$population.'-buzzcard-'.time().'.csv';
        $path = 'nova-exports/' . $filename;
        $users->storeExcel(
            $path,
            'local',
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
                'general' => 'General',
            ]),
        ];
    }
}
