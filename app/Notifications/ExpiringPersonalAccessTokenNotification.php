<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\ExpiringPersonalAccessToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Laravel\Passport\Token;

class ExpiringPersonalAccessTokenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly Token $token)
    {
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
        return new ExpiringPersonalAccessToken($this->token);
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(User $user, string $channel): bool
    {
        return $user->should_receive_email;
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
