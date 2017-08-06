<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GeneralInterestInvite extends Mailable
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
             ->subject('RoboJackets General Interest Meeting - RSVP Requested')
             ->markdown('mail.generalinterest.invite');

        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()
                    ->addTextHeader('Reply-To', 'RoboJackets Support <support@robojackets.org>');
        });
    }
}
