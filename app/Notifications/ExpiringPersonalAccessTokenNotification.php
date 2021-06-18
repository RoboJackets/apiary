<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\ExpiringPersonalAccessToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Laravel\Passport\Token;

class ExpiringPersonalAccessTokenNotification extends Notification
{
    use Queueable;

    private Token $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): ExpiringPersonalAccessToken
    {
        return (new ExpiringPersonalAccessToken($this->token))
            ->to($notifiable->gt_email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string:array>
     */
    public function toArray(User $notifiable): array
    {
        return [];
    }
}
