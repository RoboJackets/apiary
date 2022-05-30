<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Signature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class MembershipAgreementSigned extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * The signature that was just signed.
     *
     * @var \App\Models\Signature
     */
    public $signature;

    /**
     * Create a new message instance.
     */
    public function __construct(Signature $signature)
    {
        $this->signature = $signature;
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
                $message->replyTo('RoboJackets <hello@robojackets.org>');
            })->subject('[RoboJackets] Membership Agreement Signed')
            ->markdown(
                'mail.agreement.signed',
                [
                    'agreement_text' => $this->signature->membershipAgreementTemplate->renderForUser(
                        $this->signature->user,
                        $this->signature->electronic
                    ),
                ]
            );
    }
}
