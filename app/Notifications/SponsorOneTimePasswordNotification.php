<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('RoboJackets One-Time Password')
            ->view('mail.sponsor-otp', [
                'otp' => $this->oneTimePassword,
                'user' => $notifiable,
            ]);
    }
}
