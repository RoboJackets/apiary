<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\TravelAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class PaymentReceipt extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly Payment $payment)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->payment->payable->user->gt_email, $this->payment->payable->user->name)
                    ->subject('Receipt for your '.$this->getPayableDisplayName().' payment')
                    ->text(
                        'mail.paymentreceipt',
                        [
                            'payable_name' => $this->getPayableDisplayName(),
                        ]
                    )
                    ->withSymfonyMessage(static function (Email $email): void {
                        $email->replyTo('RoboJackets <treasurer@robojackets.org>');
                    })
                    ->tag('payment-receipt')
                    ->metadata('payment-id', strval($this->payment->id));
    }

    /**
     * Get the display name for the payable object.
     */
    private function getPayableDisplayName(): string
    {
        if ($this->payment->payable_type === DuesTransaction::getMorphClassStatic()) {
            return $this->payment->payable->package->name.' dues';
        }

        if ($this->payment->payable_type === TravelAssignment::getMorphClassStatic()) {
            return $this->payment->payable->travel->name.' travel';
        }

        throw new \Exception('Unrecognized payable_type '.$this->payment->payable_type);
    }
}
