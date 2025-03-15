<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace App\Mail\Dues;

use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class TransactionReminder extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly User $user)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $package_name = DuesPackage::userCanPurchase($this->user)->count() === 1
            ? DuesPackage::userCanPurchase($this->user)->sole()->name.' '
            : '';

        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->user->gt_email, $this->user->name)
            ->subject('Reminder: payment required for '.$package_name.'dues')
            ->text('mail.dues.transactionreminder')
            ->withSymfonyMessage(static function (Email $email): void {
                $email->replyTo(
                    new Address(
                        config('payment_contact.email_address'),
                        config('payment_contact.display_name')
                    )
                );
            })
            ->tag('dues-transaction-reminder')
            ->metadata('user-id', strval($this->user->id));
    }
}
