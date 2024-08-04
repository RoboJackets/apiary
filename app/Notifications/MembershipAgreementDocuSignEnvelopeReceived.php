<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\MembershipAgreementDocuSignEnvelopeReceived as MembershipAgreementDocuSignEnvelopeReceivedMailable;
use App\Models\DocuSignEnvelope;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MembershipAgreementDocuSignEnvelopeReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly DocuSignEnvelope $envelope)
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
    public function toMail(User $user): MembershipAgreementDocuSignEnvelopeReceivedMailable
    {
        return new MembershipAgreementDocuSignEnvelopeReceivedMailable($this->envelope);
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
