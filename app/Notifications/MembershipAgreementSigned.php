<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\MembershipAgreementSigned as Mailable;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MembershipAgreementSigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The signature that was just signed.
     */
    private Signature $signature;

    /**
     * Create a new notification instance.
     */
    public function __construct(Signature $signature)
    {
        $this->signature = $signature;
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
    public function toMail(User $notifiable): Mailable
    {
        // Force the relation to load, because it doesn't in the mail view for some reason.
        $this->signature->load('uploadedBy');

        return new Mailable($this->signature);
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
