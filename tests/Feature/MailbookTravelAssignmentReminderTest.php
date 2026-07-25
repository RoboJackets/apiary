<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Notifications\Travel\TravelAssignmentReminder;
use Tests\TestCase;
use Xammie\Mailbook\Facades\Mailbook;
use Xammie\Mailbook\MailableSender;

final class MailbookTravelAssignmentReminderTest extends TestCase
{
    public function test_travel_assignment_reminder_mailbook_variants_resolve(): void
    {
        config(['mailbook.enabled' => true]);

        $mailable = Mailbook::mailables()
            ->first(static fn ($item): bool => $item->class() === TravelAssignmentReminder::class);

        $this->assertNotNull($mailable);

        foreach ($mailable->getVariants() as $variant) {
            $mailable->selectVariant($variant->slug);

            $this->assertNotNull(
                $mailable->subject(),
                'Expected mailbook variant ['.$variant->label.'] to resolve a subject'
            );
        }
    }

    public function test_mailbook_previews_notification_when_should_send_is_false(): void
    {
        $member = User::factory()->create([
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '5555555555',
        ]);
        $officer = User::factory()->create();

        $travel = Travel::factory()->create([
            'departure_date' => now()->subDays(10),
            'return_date' => now()->subDays(5),
            'fee_amount' => 20,
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
            'primary_contact_user_id' => $officer->id,
            'status' => 'approved',
        ]);

        $assignment = TravelAssignment::withoutEvents(static function () use ($travel, $member): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        $notification = new TravelAssignmentReminder($assignment->fresh());

        $this->assertTrue($assignment->fresh()->cannotReceiveDocuSignReminder());
        $this->assertFalse($notification->shouldSend($member, 'mail'));

        $resolved = (new MailableSender($notification, $member))->collect();

        $this->assertNotNull($resolved->subject());
    }
}
