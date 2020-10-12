<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\DateTime;

class ResetRemoteAttendance extends Action
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
     * @param \Illuminate\Support\Collection<\App\Team>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $expiration = $fields['expiration_time'];

        if (null === $expiration) {
            $expiration = Carbon::now()->addHours(4);
        }

        $team = $models->first();

        $team->attendance_secret = hash('sha256', random_bytes(64));
        $team->attendance_secret_expiration = $expiration;
        $team->save();

        return Action::message('A new link has been generated!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        if (Auth::user()->hasRole('admin')) {
            return [
                DateTime::make('Expiration Time', 'expiration_time')
                    ->required(false)
                    ->help('When the remote attendance URL will expire. Defaults to four hours in the future.'),
            ];
        }

        return [];
    }
}
