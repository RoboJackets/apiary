<?php

declare(strict_types=1);

namespace App\Mail\Travel;

use App\Models\Travel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class AllTravelAssignmentsComplete extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly Travel $travel)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->travel->primaryContact->gt_email, $this->travel->primaryContact->name)
            ->cc(config('payment_contact.email_address'), config('payment_contact.display_name'))
            ->subject($this->renderSubjectLine())
            ->text('mail.travel.allassignmentscomplete')
            ->withSymfonyMessage(static function (Email $email): void {
                $email->replyTo(
                    new Address(
                        config('services.payment_contact.email_address'),
                        config('services.payment_contact.display_name')
                    )
                );
            })
            ->tag('travel-assignments-complete')
            ->metadata('travel-id', strval($this->travel->id));
    }

    private function renderSubjectLine(): string
    {
        if (
            ! $this->travel->assignments_need_payment &&
            ! $this->travel->assignments_need_forms &&
            $this->travel->needs_docusign
        ) {
            return 'All trip assignments completed for '.$this->travel->name;
        } elseif ($this->travel->assignments_need_payment && ! $this->travel->assignments_need_forms) {
            return 'All forms received for '.$this->travel->name;
        } elseif (
            ! $this->travel->assignments_need_payment &&
            ($this->travel->assignments_need_forms || ! $this->travel->needs_docusign)
        ) {
            return 'All trip fees paid for '.$this->travel->name;
        } else {
            throw new Exception('Unexpected state');
        }
    }
}
