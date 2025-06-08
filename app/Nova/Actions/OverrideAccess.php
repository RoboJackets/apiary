<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\PushToJedi;
use App\Models\MembershipAgreementTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class OverrideAccess extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $users
     *
     * @phan-suppress PhanTypeMismatchArgument
     */
    public function handle(ActionFields $fields, Collection $users)
    {
        $user = $users->sole();

        if ($user->is_service_account) {
            return Action::danger('Service accounts do not have access to other systems.');
        }

        if ($user->id === Auth::user()->id) {
            return Action::danger('You cannot override your own access.');
        }

        if (! $user->signed_latest_agreement && MembershipAgreementTemplate::exists()) {
            return Action::danger('This user has not signed the latest agreement.');
        }

        $date = Carbon::createFromFormat('Y-m-d', $fields->access_override_until);
        if ($date === false) {
            return Action::danger('You must select a date.');
        }
        $date->hour = 23;
        $date->minute = 59;
        $date->second = 0;
        $user->access_override_until = $date;
        $user->access_override_by_id = Auth::user()->id;
        $user->save();

        PushToJedi::dispatchSync($user, self::class, request()->user()->id, 'manual');

        return Action::message('The access override was saved!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Date::make('Override Expiration', 'access_override_until')
                ->rules('required'),
        ];
    }
}
