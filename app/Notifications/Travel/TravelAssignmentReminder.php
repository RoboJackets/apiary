<?php

declare(strict_types=1);

namespace App\Notifications\Travel;

use App\Mail\Travel\TravelAssignmentReminder as TravelAssignmentReminderMailable;
use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TravelAssignmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @psalm-mutation-free
     */
    public function __construct(private readonly TravelAssignment $assignment)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     *
     * @psalm-pure
     */
    public function via(User $user): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @psalm-mutation-free
     */
    public function toMail(User $user): TravelAssignmentReminderMailable
    {
        return new TravelAssignmentReminderMailable($this->assignment);
    }

    /**
     * Determine if the notification should be sent.
     *
     * @psalm-mutation-free
     */
    public function shouldSend(User $user, string $channel): bool
    {
        return $user->should_receive_email &&
            ! $this->assignment->is_complete &&
            $this->assignment->deleted_at === null;
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string,string>
     *
     * @psalm-pure
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'email',
        ];
    }
}
