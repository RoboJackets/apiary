<?php

declare(strict_types=1);

namespace App\Notifiables;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class TreasurerNotifiable
{
    use Notifiable;

    /**
     * Route notifications for the Slack channel.
     */
    public function routeNotificationForSlack(Notification $notification): ?string
    {
        return config('services.treasurer_slack_webhook_url');
    }
}
