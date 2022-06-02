<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\DuesPaymentDue as DuesPaymentDueMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DuesPaymentDue extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array<string>
     */
    public function via(User $user): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  User  $user
     * @return \App\Mail\DuesPaymentDue
     */
    public function toMail(User $user): DuesPaymentDueMailable
    {
        return new DuesPaymentDueMailable($user->dues()->orderByDesc('updated_at')->first());
    }

    /**
     * Determine if the notification should be sent.
     *
     * @param  User  $user
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend(User $user, string $channel)
    {
        return ! $user->is_active;
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @param  User  $user
     * @return array<string, \Carbon\Carbon>
     */
    public function withDelay(User $user)
    {
        return [
            'mail' => now()->addHours(48)->hour(10)->minute(0)->second(0),
        ];
    }
}
