<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\DatabaseMailable as Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $template_id;

    public $metadata;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($template_id, $metadata)
    {
        $this->template_id = $template_id;
        $this->metadata = $metadata;
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
        return (new Mailable($this->template_id, $this->metadata))->to($notifiable->email);
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
