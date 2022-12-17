<?php

declare(strict_types=1);

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Laravel\Passport\Token;
use Symfony\Component\Mime\Email;

class ExpiringPersonalAccessToken extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Indicates whether or not the token has already expired.
     */
    public readonly bool $already_expired;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly Token $token)
    {
        $this->already_expired = Carbon::now() > $token->expires_at;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this
            ->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->token->user->gt_email, $this->token->user->name)
            ->withSymfonyMessage(static function (Email $message): void {
                $message->replyTo('RoboJackets <support@robojackets.org>');
            })->subject('Your '.config('app.name').' personal access token '
                .($this->already_expired ? 'recently expired' : 'will expire soon'))
            ->text('mail.oauth2.pat_expiration')
            ->tag('token-expiring')
            ->metadata('token-id', strval($this->token->id));
    }
}
