<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

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
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $population = $fields->population;
        $users = User::select('gtid', 'first_name', 'last_name')
            ->buzzCardAccessEligible()
            ->where('is_service_account', '=', false)
            ->when(
                $population === 'core',
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

        if (count($users) === 0) {
            return Action::danger('No users match the provided criteria!');
        }

        $output = $users->reduce(
            static fn (?string $c, User $u): string => ($c ?? '').$u->gtid.','.$u->first_name.','.$u->last_name."\n"
        );

        $phrasing = $population === 'core' ? 'with' : 'without';
        $timestamp = Carbon::now()->toDateTimeLocalString();
        $filename = '575F-GRP_SCC_'.$phrasing.'_RoboJackets-'.$timestamp.'.csv';
        $path = 'nova-exports/'.$filename;

        Storage::put($path, $output);

        // Generate signed URL to pass to backend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return Action::downloadURL($url, $filename);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, Select>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Population')
                ->options([
                    'core' => 'Core',
                    'general' => 'General',
                ])
                ->required()
                ->rules('required'),
        ];
    }
}
