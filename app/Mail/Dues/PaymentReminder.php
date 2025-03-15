<?php

declare(strict_types=1);

namespace App\Mail\Dues;

use App\Models\DuesTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class PaymentReminder extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly DuesTransaction $transaction)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->transaction->user->gt_email, $this->transaction->user->name)
            ->subject('Reminder: payment required for '.$this->transaction->package->name.' dues')
            ->text('mail.dues.paymentreminder')
            ->withSymfonyMessage(static function (Email $email): void {
                $email->replyTo(
                    new Address(
                        config('services.payment_contact.email_address'),
                        config('services.payment_contact.display_name')
                    )
                );
            })
            ->tag('dues-payment-reminder')
            ->metadata('transaction-id', strval($this->transaction->id));
    }
}
