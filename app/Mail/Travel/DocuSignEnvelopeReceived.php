<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Mail\Travel;

use App\Models\DocuSignEnvelope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class DocuSignEnvelopeReceived extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly DocuSignEnvelope $envelope)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->envelope->signedBy->gt_email, $this->envelope->signedBy->name)
            ->subject('Received '.$this->subjectLineFormDescription().' for '.$this->envelope->signable->travel->name)
            ->text('mail.travel.docusignenvelopereceived')
            ->withSymfonyMessage(function (Email $email): void {
                $email->replyTo(
                    $this->envelope->signable->travel->primaryContact->name.
                    ' <'.$this->envelope->signable->travel->primaryContact->gt_email.'>'
                );
            })
            ->tag('travel-docusign-received')
            ->metadata('envelope-id', strval($this->envelope->id));
    }

    private function subjectLineFormDescription(): string
    {
        if ($this->envelope->signable->travel->needs_airfare_form) {
            if ($this->envelope->signable->travel->needs_travel_information_form) {
                return 'forms';
            } else {
                return 'airfare request form';
            }
        } else {
            if ($this->envelope->signable->travel->needs_travel_information_form) {
                return 'travel information form';
            } else {
                return 'form';
            }
        }
    }
}
