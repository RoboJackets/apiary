<?php

declare(strict_types=1);

namespace App\Util;

use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\FailedJobMonitor\Notification;

class JobFailureNotification extends Notification
{
    #[\Override]
    public function toSlack(): SlackMessage
    {
        $job_name_parts = explode('\\', $this->event->job->resolveName());
        $job_name = $job_name_parts[count($job_name_parts) - 1];

        return (new SlackMessage())
            ->error()
            ->attachment(function (SlackAttachment $attachment) use ($job_name): void {
                $attachment
                    ->title(
                        $job_name.' failed',
                        config('app.url').'/horizon/failed/'.$this->event->job->uuid()
                    )
                    ->fallback($job_name.' failed')
                    ->fields([
                        'Exception' => $this->event->exception->getMessage(),
                        'Job' => $job_name,
                    ]);
            });
    }
}
