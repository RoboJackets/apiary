<?php

declare(strict_types=1);

namespace App\Mail\DocuSign\Agreement;

use App\Models\User;
use Illuminate\Mail\Mailable;

/**
 * This class should ONLY be used for Mailbook. Actual DocuSign email configuration is in \App\Util\DocuSign.
 */
class ParentNotification extends Mailable
{
    public function __construct(public readonly User $user)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('dse_demo@docusign.net', 'RoboJackets - RSO via DocuSign')
            ->to('parent@example.com', 'Parent Example')
            ->replyTo(
                config('docusign.service_account_reply_to.address'),
                config('docusign.service_account_reply_to.name')
            )
            ->subject(trim(view('mail.docusign.agreement.parent.subject', ['user' => $this->user])->render()))
            ->text('mail.docusign.agreement.parent.body', ['user' => $this->user]);
    }
}
