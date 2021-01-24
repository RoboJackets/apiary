<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\MembershipAgreementTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;

class OverrideAccess extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\User>  $users
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $users): array
    {
        foreach ($users as $user) {
            if ($user->id === Auth::user()->id) {
                return Action::danger('You cannot override your own access!');
            }

            if (! $user->hasSignedLatestAgreement() && MembershipAgreementTemplate::exists()) {
                return Action::danger('This user has not signed the latest agreement!');
            }

            $date = Carbon::createFromFormat('Y-m-d', $fields->access_override_until);
            if (false === $date) {
                return Action::danger('You must select a date!');
            }
            $date->hour = 23;
            $date->minute = 59;
            $date->second = 0;
            $user->access_override_until = $date;
            $user->access_override_by_id = Auth::user()->id;
            $user->save();
        }

        return Action::message('The access override'.(1 === count($users) ? ' was' : 's were').' saved!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [
            Date::make('Override Expiration', 'access_override_until')->rules('required'),
        ];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
