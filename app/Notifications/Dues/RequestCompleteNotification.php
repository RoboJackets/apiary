<?php

namespace App\Notifications\Dues;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Dues\RequestComplete as Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use App\DuesPackage;

class RequestCompleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $duesPackage;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($duesPackage)
    {
        $this->duesPackage = $duesPackage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        return (new Mailable($notifiable->uid, $this->duesPackage))->to($notifiable->gt_email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
