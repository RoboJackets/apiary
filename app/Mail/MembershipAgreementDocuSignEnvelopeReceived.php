<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\DocuSignEnvelope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class MembershipAgreementDocuSignEnvelopeReceived extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public DocuSignEnvelope $envelope;

    /**
     * Create a new message instance.
     */
    public function __construct(DocuSignEnvelope $envelope)
    {
        $this->envelope = $envelope;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->envelope->signedBy->gt_email, $this->envelope->signedBy->name)
                    ->cc('Membership Agreement Archives', config('services.membership_agreement_archive_email'))
                    ->subject('Membership agreement signed')
                    ->text('mail.agreement.docusignenvelopereceived')
                    ->withSymfonyMessage(static function (Email $email): void {
                        $email->replyTo('RoboJackets <hello@robojackets.org>');
                    })
                    ->tag('agreement-docusign-received')
                    ->metadata('envelope-id', strval($this->envelope->id));
    }
}
