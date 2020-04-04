<?php

declare(strict_types=1);

namespace App\Notifiables;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class CoreOfficersNotifiable
{
    use Notifiable;

    /**
     * Route notifications for the Slack channel.
     */
    public function routeNotificationForSlack(Notification $notification): ?string
    {
        return config('services.core_officers_slack_webhook_url');
    }
}
