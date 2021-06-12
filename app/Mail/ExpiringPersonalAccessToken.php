<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mime_SimpleMimeEntity as SimpleMimeEntity;

class ExpiringPersonalAccessToken extends Mailable
{
    use Queueable, SerializesModels;

    private $token;
    private $already_expired;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->already_expired = Carbon::now() < $token->expires_at;
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
            ->withSwiftMessage(static function (SimpleMimeEntity $message): void {
                $message->getHeaders()->addTextHeader('Reply-To', 'RoboJackets <support@robojackets.org>');
            })->subject('Your MyRoboJackets Personal Access Token '.($this->token ? 'Recently Expired' : 'Will Expire Soon'))
            ->markdown('mail.oauth2.pat_expiration',
                [
                    'token' => $this->token,
                    'already_expired' => $this->already_expired,
                ]
            );
    }
}
