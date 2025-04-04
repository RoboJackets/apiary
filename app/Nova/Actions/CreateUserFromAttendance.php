<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreateUserFromAttendance extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Create User';

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            // TODO: Check if max lengths are different in prod
            Text::make('Username')
                ->required()
                ->maxlength(127)
                ->enforceMaxLength(),
            Text::make('Georgia Tech Email')
                ->required()
                ->maxlength(255)
                ->enforceMaxLength(),
            Text::make('First Name')
                ->required()
                ->maxlength(127)
                ->enforceMaxLength(),
            Text::make('Middle Name')
                ->maxlength(127)
                ->enforceMaxLength(),
            Text::make('Last Name')
                ->required()
                ->maxlength(127)
                ->enforceMaxLength(),
            Text::make('Reason for Creation')
                ->required()
                ->maxlength(255)
                ->enforceMaxLength(),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() > 1) {
            return Action::danger('Cannot create a user from more than one attendance record.');
        }
        if (User::where('gtid', $models->sole()->gtid)->exists()) {
            return Action::danger('User already exists for this attendance record.');
        }
        $gtid = $models->sole()->gtid;
        $user = new User();
        $user->uid = $fields->username;
        $user->gtid = $gtid;
        $user->gt_email = $fields->georgia_tech_email;
        $user->first_name = $fields->first_name;
        $user->legal_middle_name = $fields->middle_name;
        $user->last_name = $fields->last_name;
        $user->create_reason = $fields->reason_for_creation;
        $user->has_ever_logged_in = 0;
        $saved = $user->save();
        if (! $saved) {
            return Action::danger('Failed to save user.');
        } else {
            return Action::message('Successfully created user!');
        }
    }
}
