<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportDemographicsSurveyRecipients extends Action
{
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
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     *
     * @phan-suppress PhanTypeMismatchArgument
     * @phan-suppress PhanDeprecatedFunction
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $users = User::active()
            ->whereNull('email_suppression_reason')
            ->get();

        if (count($users) === 0) {
            return Action::danger('No users match the criteria!');
        }

        $output = $users->reduce(
            static fn (?string $c, User $u): string => ($c ?? '').$u->preferred_first_name.','.$u->gt_email."\n"
        );

        $filename = 'demographics-survey-'.Carbon::now()->toDateTimeLocalString().'.csv';
        $path = 'nova-exports/'.$filename;

        Storage::put($path, $output);

        // Generate signed URL to pass to backend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return Action::download($url, $filename)
            ->withMessage('The demographics survey recipient list was successfully exported!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
