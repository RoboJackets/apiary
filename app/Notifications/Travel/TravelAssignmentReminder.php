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
     */
    public function shouldSend(User $user, string $channel): bool
    {
        if (! $user->should_receive_email) {
            return false;
        }

        if ($this->assignment->is_complete) {
            return false;
        }

        if ($this->assignment->deleted_at !== null) {
            return false;
        }

        if ($this->assignment->charged_off_at !== null && ! $this->assignment->needs_docusign) {
            return false;
        }

        return ! $this->assignment->cannotReceiveDocuSignReminder();
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
