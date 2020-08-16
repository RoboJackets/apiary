<?php

declare(strict_types=1);

namespace App\Mail\Dues;

use App\DuesPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mime_SimpleMimeEntity as SimpleMimeEntity;

class RequestComplete extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The DuesPackage selected.
     */
    public DuesPackage $duesPackage;

    /**
     * Create a new message instance.
     */
    public function __construct(DuesPackage $duesPackage)
    {
        $this->duesPackage = $duesPackage;
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
                $message->getHeaders()->addTextHeader('Reply-To', 'RoboJackets <treasurer@robojackets.org>');
            })->subject('[RoboJackets] ACTION REQUIRED | Dues Form Received')
            ->markdown('mail.dues.requestcomplete');
    }
}
