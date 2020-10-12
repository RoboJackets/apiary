<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\GenerateResumeBook as GenerateJob;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;

class GenerateResumeBook extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\User>  $models
     *
     * @return array<string,string>
     *
     * @suppress PhanTypeMismatchArgumentNullableInternal
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        // Check this here because canSee was removed from App\Nova\User as this is a standalone action.
        if (! Auth::user()->can('read-users-resume')) {
            return Action::danger('Sorry! You are not authorized to perform this action.');
        }

        $job = new GenerateJob($fields->major, $fields->resume_date_cutoff);
        $job->handle();

        if (null === $job->path) {
            return Action::danger('An unknown error occurred.');
        }

        return Action::download(
            route('api.v1.resumebook.show', ['tag' => $job->datecode.($fields->major ? '-'.$fields->major : '')]),
            basename($job->path)
        );
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [
            Text::make('Major')
                ->nullable()
                ->help('Only combine resumes for this major abbreviation (e.g., ME, EE, CMPE, CS).'),

            DateTime::make('Resume Date Cutoff')
                ->required(true)
                ->help('Only export resumes uploaded after this date.'),
        ];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
