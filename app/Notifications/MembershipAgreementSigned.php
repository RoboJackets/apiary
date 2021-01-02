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
     * The signature that was just signed
     *
     * @var \App\Models\Signature
     */
    private $signature;

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
        return (new Mailable($this->signature))
            ->to($notifiable->gt_email)
            ->cc(config('services.membership_agreement_archive_email'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string,string>
     */
    public function toArray(User $notifiable): array
    {
        return [];
    }
}
