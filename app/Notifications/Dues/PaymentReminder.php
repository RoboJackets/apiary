<?php

declare(strict_types=1);

namespace App\Notifications\Dues;

use App\Mail\Dues\PaymentReminder as DuesPaymentReminderMailable;
use App\Models\DuesTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly DuesTransaction $transaction)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(User $user): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $user): DuesPaymentReminderMailable
    {
        return new DuesPaymentReminderMailable($this->transaction);
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(User $user, string $channel): bool
    {
        return ! $user->is_active && $user->should_receive_email;
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
