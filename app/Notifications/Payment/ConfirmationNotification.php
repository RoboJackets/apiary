<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Notifications\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Payment\Confirmation as Mailable;
use App\Payment;

class ConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The payment that was completed
     *
     * @var \App\Payment
     */
    public $payment;

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
     * @param Notifiable  $notifiable
     *
     * @return array<string>
     */
    public function via(Notifiable $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param Notifiable  $notifiable
     *
     * @return Mailable
     */
    public function toMail(Notifiable $notifiable): Mailable
    {
        return (new Mailable($this->payment))->to($notifiable->gt_email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param Notifiable  $notifiable
     *
     * @return array<string,string>
     */
    public function toArray(Notifiable $notifiable): array
    {
        return [
        ];
    }
}
