<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DuesPaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->from('noreply@my.robojackets.org', 'RoboJackets')
             ->subject('RoboJackets Dues Payment Confirmation')
             ->markdown('mail.dues.confirmation');

        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()
                    ->addTextHeader('Reply-To', 'RoboJackets <info@robojackets.org>');
        });
    }
}
