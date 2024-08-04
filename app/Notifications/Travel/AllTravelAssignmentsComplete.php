<?php

declare(strict_types=1);

namespace App\Notifications\Travel;

use App\Mail\Travel\AllTravelAssignmentsComplete as AllTravelAssignmentsCompleteMailable;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class AllTravelAssignmentsComplete extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly Travel $travel)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(User $user): array
    {
        return ['mail', NovaChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $user): AllTravelAssignmentsCompleteMailable
    {
        return new AllTravelAssignmentsCompleteMailable($this->travel);
    }

    /**
     * Get the Nova representation of the notification.
     */
    public function toNova(): NovaNotification
    {
        return (new NovaNotification())
            ->message('All assignments for '.$this->travel->name.' have been completed!')
            ->action('View travel', URL::remote(route(
                'nova.pages.detail',
                [
                    'resource' => 'travel',
                    'resourceId' => $this->travel->id,
                ]
            )))
            ->icon('inbox-in')
            ->type('info');
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(User $user, string $channel): bool
    {
        return $user->should_receive_email || $channel === NovaChannel::class;
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
