<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Carbon\Carbon;
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
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $population = $fields->population;
        $users = User::select('gtid')->BuzzCardAccessEligible()
            ->when(
                'core' === $population,
                static function (Builder $q): void {
                    $q->whereHas('teams', static function (Builder $query): void {
                        $query->where('name', 'Core');
                    });
                },
                static function (Builder $q): void {
                    $q->whereDoesntHave('teams', static function (Builder $query): void {
                        $query->where('name', 'Core');
                    });
                }
            )
            ->get();

        if (0 === count($users)) {
            return Action::danger('No users match the provided criteria!');
        }

        // Exclude fields that we don't care about (Everything except GTID)
        $users->pluck('gtid');

        $phrasing = 'core' === $population ? 'with' : 'without';
        $timestamp = Carbon::now()->toDateTimeLocalString();
        $filename = '575F-GRP_SCC_'.$phrasing.'_RoboJackets-'.$timestamp.'.csv';
        $path = 'nova-exports/'.$filename;
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
     * @return array<int, Select>
     */
    public function fields(): array
    {
        return [
            Select::make('Population')->options([
                'core' => 'Core',
                'general' => 'General',
            ]),
        ];
    }
}
