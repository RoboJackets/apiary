<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Spatie\OneTimePassword\OneTimePassword;
use Symfony\Component\Mime\Email;

class SponsorOneTimePassword extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly OneTimePassword $oneTimePassword)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->oneTimePassword->authenticatable->email)
            ->subject('RoboJackets One-Time Password')
            ->text('mail.sponsor-otp')
            ->withSymfonyMessage(static function (Email $email): void {
                $email->replyTo('RoboJackets <hello@robojackets.org>');
            })
            ->tag('sponsor-one-time-password');
    }
}
