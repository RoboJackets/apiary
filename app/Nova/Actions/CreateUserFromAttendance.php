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
            return Action::danger('Action can only run on one record.');
        }
        $gtid = $model->sole()->gtid;
        $user = new User();
        $user->uid = $fields[0];
        $user->gtid = $gtid;
        $user->gt_email = $fields[1];
        $user->first_name = $fields[2];
        $user->legal_middle_name = $fields[3];
        $user->last_name = $fields[4];
        $user->full_name = $fields[2]." ".$fields[4];
        $user->create_reason = $fields[5];
        $saved = $user->save();
        if (!saved) {
            return Action::danger('Failed to save user.');
        } else {
            return Action::message('Successfully created user!');
        }
    }
}