<?php

declare(strict_types=1);

namespace App\Notifications\Travel;

use App\Mail\Travel\AllTravelAssignmentsComplete as AllTravelAssignmentsCompleteMailable;
use App\Models\Travel;
use App\Models\TravelAssignment;
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

    private Travel $travel;

    /**
     * Create a new notification instance.
     */
    public function __construct(Travel $travel)
    {
        $this->travel = $travel;
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
     * @return \App\Mail\Travel\AllTravelAssignmentsComplete
     */
    public function toMail(User $user): AllTravelAssignmentsCompleteMailable
    {
        return new AllTravelAssignmentsCompleteMailable($this->travel);
    }

    /**
     * Get the Nova representation of the notification.
     *
     * @return \Laravel\Nova\Notifications\NovaNotification
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
     *
     * @param  User  $user
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend(User $user, string $channel): bool
    {
        return ($user->should_receive_email || NovaChannel::class === $channel) && $this->travel->assignments->reduce(
            static function (bool $carry, TravelAssignment $assignment): bool {
                return $carry && $assignment->is_complete;
            },
            true
        );
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
