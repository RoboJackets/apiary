<?php

declare(strict_types=1);

namespace App\Notifications\Payment;

use App\Mail\Payment\Confirmation as Mailable;
use App\Payment;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The payment that was completed.
     */
    public \App\Payment $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
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
        return (new Mailable($this->payment))->to($notifiable->gt_email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string,string>
     */
    public function toArray(User $notifiable): array
    {
        return [
        ];
    }
}
