<?php

declare(strict_types=1);

namespace App\Mail\DocuSign;

use App\Models\TravelAssignment;
use Illuminate\Mail\Mailable;

/**
 * This class should ONLY be used for Mailbook. Actual DocuSign email configuration is in \App\Util\DocuSign.
 */
class Travel extends Mailable
{
    public function __construct(public readonly TravelAssignment $assignment)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from(
            'dse_demo@docusign.net',
            $this->assignment->travel->primaryContact->full_name.' via DocuSign'
        )
            ->to($this->assignment->user->gt_email, $this->assignment->user->full_name)
            ->replyTo(
                $this->assignment->travel->primaryContact->gt_email,
                $this->assignment->travel->primaryContact->full_name
            )
            ->subject(trim(view('mail.docusign.travel.subject', ['travel' => $this->assignment->travel])->render()))
            ->text('mail.docusign.travel.body', ['travel' => $this->assignment->travel]);
    }
}
