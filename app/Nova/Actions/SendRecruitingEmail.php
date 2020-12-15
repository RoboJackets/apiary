<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\RecruitingVisit;
use App\Notifications\GeneralInterestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SendRecruitingEmail extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $visit = RecruitingVisit::where('recruiting_email', $models->first()->recruiting_email)->first();
        Notification::send($visit, new GeneralInterestNotification());
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
