<?php declare(strict_types = 1);

namespace App\Notifications\Dues;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Dues\RequestComplete as Mailable;

class RequestCompleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $duesPackage;

    /**
     * Create a new notification instance.
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
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Mailable
     */
    public function toMail($notifiable): Mailable
    {
        return (new Mailable($notifiable->uid, $this->duesPackage))->to($notifiable->gt_email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
        ];
    }
}
