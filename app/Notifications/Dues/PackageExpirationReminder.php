<?php

declare(strict_types=1);

namespace App\Notifications\Dues;

use App\Mail\Dues\PackageExpirationReminder as PackageExpirationReminderMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PackageExpirationReminder extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): PackageExpirationReminderMailable
    {
        return new PackageExpirationReminderMailable();
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string,string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'email',
        ];
    }
}
