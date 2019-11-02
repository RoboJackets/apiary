<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Notifications\Dues;

use App\DuesPackage;
use App\Mail\Dues\RequestComplete as Mailable;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequestCompleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The dues package that was requested.
     *
     * @var \App\DuesPackage
     */
    public $duesPackage;

    /**
     * Create a new notification instance.
     */
    public function __construct(DuesPackage $duesPackage)
    {
        $this->duesPackage = $duesPackage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param User  $notifiable
     *
     * @return array<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User  $notifiable
     *
     * @return Mailable
     */
    public function toMail(User $notifiable): Mailable
    {
        return (new Mailable($this->duesPackage))->to($notifiable->gt_email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param User  $notifiable
     *
     * @return array<string,string>
     */
    public function toArray(User $notifiable): array
    {
        return [];
    }
}
