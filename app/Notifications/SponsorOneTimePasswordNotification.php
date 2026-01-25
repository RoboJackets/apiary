<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\Sponsors\SponsorOneTimePassword as SponsorOneTimePasswordMailable;
use Illuminate\Bus\Queueable;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;

class SponsorOneTimePasswordNotification extends OneTimePasswordNotification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    #[\Override]
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    #[\Override]
    public function toMail(object $notifiable): SponsorOneTimePasswordMailable
    {
        return new SponsorOneTimePasswordMailable($this->oneTimePassword);
    }
}
