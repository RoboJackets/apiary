<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\DuesTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class DuesPaymentDue extends Mailable implements ShouldQueue, ShouldBeUnique
{
    use Queueable;
    use SerializesModels;

    public DuesTransaction $transaction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DuesTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): DuesPaymentDue
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->transaction->user->gt_email, $this->transaction->user->name)
                    ->subject('Reminder: Payment required for '.$this->transaction->package->name.' dues')
                    ->text('mail.duespaymentdue')
                    ->withSymfonyMessage(static function (Email $email): void {
                        $email->replyTo('RoboJackets <treasurer@robojackets.org>');
                    });
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->transaction->user->id);
    }
}
