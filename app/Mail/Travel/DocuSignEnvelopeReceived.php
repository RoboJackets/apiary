<?php

declare(strict_types=1);

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
    public function __construct(public DocuSignEnvelope $envelope)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->envelope->signedBy->gt_email, $this->envelope->signedBy->name)
                    ->subject('Received travel documents for '.$this->envelope->signable->travel->name)
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
}
