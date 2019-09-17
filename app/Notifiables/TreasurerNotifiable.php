<?php

namespace App\Notifiables;

use Illuminate\Notifications\Notifiable;

class TreasurerNotifiable
{
    use Notifiable;

    /**
     * Route notifications for the Slack channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return config('services.treasurer_slack_webhook_url');
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return config('services.treasurer_email');
    }
}
