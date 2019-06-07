<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversablePropertyTypeHintSpecification
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversableParameterTypeHintSpecification
// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversableReturnTypeHintSpecification
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\DatabaseMailable as Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The ID of the template that will be sent
     *
     * @var int
     */
    public $template_id;

    /**
     * The metadata to pass to the template
     *
     * @var array
     */
    public $metadata;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $template_id, array $metadata): void
    {
        $this->template_id = $template_id;
        $this->metadata = $metadata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed  $notifiable
     *
     * @return Mailable
     */
    public function toMail($notifiable): Mailable
    {
        return (new Mailable($this->template_id, $this->metadata))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed  $notifiable
     *
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
