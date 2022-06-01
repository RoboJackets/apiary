<?php

declare(strict_types=1);

namespace App\Mail\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class Confirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The Payment object of interest.
     *
     * @var Payment
     */
    public $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('noreply@my.robojackets.org', 'RoboJackets')
            ->withSymfonyMessage(static function (Email $message): void {
                $message->replyTo('RoboJackets <treasurer@robojackets.org>');
            })->subject('[RoboJackets] Payment Processed')
            ->markdown('mail.payment.confirmation');
    }
}
