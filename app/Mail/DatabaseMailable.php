<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversableParameterTypeHintSpecification
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversablePropertyTypeHintSpecification
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

namespace App\Mail;

use App\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mime_SimpleMimeEntity as SimpleMimeEntity;

class DatabaseMailable extends Mailable
{
    use Queueable, SerializesModels;

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
     * @var array
     */
    public $metadata;

    /**
     * Create a new message instance.
     */
    public function __construct(int $template_id, $metadata)
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
        if (! $nt) {
            die('Could not find template');
        }

        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->withSwiftMessage(static function (SimpleMimeEntity $message): void {
                $message->getHeaders()
                    ->addTextHeader('Reply-To', 'RoboJackets <info@robojackets.org>');
            })
            ->subject($nt->subject)
            ->markdown('mail.database', ['markdown' => $nt->body_markdown, 'metadata' => $this->metadata]);
    }
}
