<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Mail\Travel;

use App\Models\TravelAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class TravelAssignmentCreated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly TravelAssignment $assignment)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->to($this->assignment->user->gt_email, $this->assignment->user->name)
            ->subject(
                $this->subjectLineCallToAction().
                ' required for '.$this->assignment->travel->name
            )
            ->text('mail.travel.assignmentcreated')
            ->withSymfonyMessage(function (Email $email): void {
                $email->replyTo(
                    $this->assignment->travel->primaryContact->name.
                    ' <'.$this->assignment->travel->primaryContact->gt_email.'>'
                );
            })
            ->tag('travel-assignment-created')
            ->metadata('assignment-id', strval($this->assignment->id));
    }

    private function subjectLineCallToAction(): string
    {
        if (
            $this->assignment->needs_docusign &&
            $this->assignment->user->has_emergency_contact_information &&
            $this->assignment->travel->fee_amount === 0
        ) {
            if ($this->assignment->travel->needs_airfare_form) {
                if ($this->assignment->travel->needs_travel_information_form) {
                    return 'Forms';
                } else {
                    return 'Airfare request form';
                }
            } else {
                if ($this->assignment->travel->needs_travel_information_form) {
                    return 'Travel information form';
                } else {
                    return 'Form';
                }
            }
        } elseif ($this->assignment->needs_docusign || ! $this->assignment->user->has_emergency_contact_information) {
            return 'Action';
        } else {
            return 'Payment';
        }
    }
}
