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
     */
    public Signature $signature;

    /**
     * Create a new message instance.
     */
    public function __construct(Signature $signature)
    {
        $this->signature = $signature;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this
            ->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->signature->user->gt_email, $this->signature->user->name)
            ->cc(config('services.membership_agreement_archive_email'))
            ->withSymfonyMessage(static function (Email $email): void {
                $email->replyTo('RoboJackets <hello@robojackets.org>');
            })->subject('Membership agreement signed')
            ->text(
                'mail.agreement.signed',
                [
                    'agreement_text' => $this->signature->membershipAgreementTemplate->renderForUser(
                        $this->signature->user,
                        $this->signature->electronic
                    ),
                ]
            )
            ->tag('agreement-signed')
            ->metadata('signature-id', strval($this->signature->id));
    }
}
