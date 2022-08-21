<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\DuesTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class DuesPaymentReminder extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public DuesTransaction $transaction)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->transaction->user->gt_email, $this->transaction->user->name)
                    ->subject('Reminder: Payment required for '.$this->transaction->package->name.' dues')
                    ->text('mail.duespaymentreminder')
                    ->withSymfonyMessage(static function (Email $email): void {
                        $email->replyTo('RoboJackets <treasurer@robojackets.org>');
                    })
                    ->tag('dues-payment-reminder')
                    ->metadata('transaction-id', strval($this->transaction->id));
    }
}
