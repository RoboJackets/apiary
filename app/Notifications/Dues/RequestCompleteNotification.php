<?php

declare(strict_types=1);

namespace App\Notifications\Dues;

use App\Mail\Dues\RequestComplete as Mailable;
use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequestCompleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The dues package that was requested.
     *
     * @var \App\Models\DuesPackage
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
        return (new Mailable($this->duesPackage))->to($notifiable->gt_email);
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
