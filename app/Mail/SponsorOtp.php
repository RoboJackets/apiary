<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SponsorOtp extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly string $otp,
        public readonly string $email
    ) {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->email)
            ->subject('Your One-Time Password for Sponsor Login')
            ->text('mail.sponsor-otp')
            ->tag('sponsor-otp')
            ->metadata('sponsor-email', $this->email);
    }
}
