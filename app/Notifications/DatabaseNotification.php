<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\DatabaseMailable as Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The ID of the template that will be sent.
     */
    public int $template_id;

    /**
     * The metadata to pass to the template.
     *
     * @var array<string,string>
     */
    public array $metadata;

    /**
     * Create a new notification instance.
     *
     * @param int $template_id the ID of the template to use
     * @param array<string,string> $metadata any metadata to pass to the template
     */
    public function __construct(int $template_id, array $metadata)
    {
        $this->template_id = $template_id;
        $this->metadata = $metadata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): Mailable
    {
        return (new Mailable($this->template_id, $this->metadata))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string,string>
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
