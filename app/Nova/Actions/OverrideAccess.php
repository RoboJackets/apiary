<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\DateTime;

class OverrideAccess extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $users
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $users): array
    {
        foreach ($users as $user) {
            if ($user->id === Auth::user()->id) {
                return Action::danger('You cannot override your own access!');
            }
            $user->access_override_until = $fields->access_override_until;
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
            DateTime::make('Override Expiration', 'access_override_until')->rules('required'),
        ];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
