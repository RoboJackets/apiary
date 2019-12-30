<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Notifiables;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class CoreNotifiable
{
    use Notifiable;

    /**
     * Route notifications for the Slack channel.
     */
    public function routeNotificationForSlack(Notification $notification): ?string
    {
        return config('services.core_slack_webhook_url');
    }
}
