<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NotificationTemplate;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mime_SimpleMimeEntity as SimpleMimeEntity;

class DatabaseMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The URL for this instance of the application.
     *
     * @var string
     */
    public $app_url;

    /**
     * The ID of the template that will be sent.
     *
     * @var int
     */
    public $template_id;

    /**
     * The metadata to pass to the template.
     *
     * @var array<string,string>
     */
    public $metadata;

    /**
     * Create a new message instance.
     *
     * @param int $template_id the ID of the template to use
     * @param array<string,string> $metadata any metadata to pass to the template
     */
    public function __construct(int $template_id, array $metadata)
    {
        $this->app_url = url('/');
        $this->template_id = $template_id;
        $this->metadata = $metadata;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nt = NotificationTemplate::find($this->template_id);
        if (null === $nt) {
            throw new Exception('Failed to find template');
        }

        return $this->from('noreply@my.robojackets.org', $nt->from)
            ->withSwiftMessage(static function (SimpleMimeEntity $message) use ($nt): void {
                $message->getHeaders()
                    ->addTextHeader('Reply-To', $nt->from.' <hello@robojackets.org>');
            })
            ->subject($nt->subject)
            ->markdown('mail.database', ['markdown' => $nt->body_markdown, 'metadata' => $this->metadata]);
    }
}
