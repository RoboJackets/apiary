<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

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
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Attendance>  $models
     *
     * @phan-suppress PhanTypeMismatchArgument
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
        try {
            CreateOrUpdateUserFromBuzzAPI::dispatchSync(
                CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_GTID,
                $gtid,
                'attendance-record-action'
            );
        } catch (Exception $ex) {
            return Action::danger('Failed to save user: ', $ex->getMessage());
        }

        return Action::message('Successfully created user!');
    }
}
