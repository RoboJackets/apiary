<?php

declare(strict_types=1);

namespace App\Notifications\Travel;

use App\Mail\Travel\TravelAssignmentCreated as TravelAssignmentCreatedMailable;
use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class TravelAssignmentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private TravelAssignment $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(TravelAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array<string>
     */
    public function via(User $user): array
    {
        return ['mail', NovaChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  User  $user
     * @return \App\Mail\Travel\TravelAssignmentCreated
     */
    public function toMail(User $user): TravelAssignmentCreatedMailable
    {
        return new TravelAssignmentCreatedMailable($this->assignment);
    }

    /**
     * Get the Nova representation of the notification.
     *
     * @return \Laravel\Nova\Notifications\NovaNotification
     */
    public function toNova(): NovaNotification
    {
        return (new NovaNotification())
            ->message('Action required for '.$this->assignment->travel->name.' travel')
            ->action('View action items', URL::remote(route('travel.index')))
            ->icon('globe')
            ->type('info');
    }

    /**
     * Determine if the notification should be sent.
     *
     * @param  User  $user
     * @param  string  $channel
     * @return bool
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
