<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
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
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Indicates if the action can be run without any models.
     *
     * @var bool
     */
    public $standalone = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Export';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'This export has built-in filters; any filters selected from the user list are ignored.';

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (! Auth::user()->can('read-users-gtid')) {
            return Action::danger('Sorry! You are not authorized to perform this action.');
        }

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

        return ActionResponse::download($filename, $url)
            ->withMessage('The BuzzCard access list was successfully exported!');
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
