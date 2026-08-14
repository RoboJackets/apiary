<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendReminders;
use App\Jobs\SendTravelAssignmentReminder;
use App\Models\DocuSignEnvelope;
use App\Models\Travel;
use App\Models\User;
use App\Notifications\Travel\TravelAssignmentReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class SendTravelAssignmentReminderDocuSignSkipTest extends TestCase
{
    public function test_should_not_send_when_past_return_paid_forms_only_no_envelope_and_invalid_credentials(): void
    {
        $contact = User::factory()->create([
            'docusign_access_token' => null,
        ]);
        $member = User::factory()->create();
        $travel = $this->createFormsRequiredTrip($contact, true);
        $assignment = $this->createTravelAssignment($travel, $member, true);

        $this->assertTrue($travel->return_date_has_passed);
        $this->assertTrue($assignment->is_paid);
        $this->assertTrue($assignment->needs_docusign);
        $this->assertTrue($assignment->envelope()->whereNotNull('envelope_id')->doesntExist());
        $this->assertTrue($assignment->cannotReceiveDocuSignReminder());

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertFalse($notification->shouldSend($member, 'mail'));
    }

    public function test_should_send_when_unpaid_even_if_forms_cannot_be_sent(): void
    {
        $contact = User::factory()->create([
            'docusign_access_token' => null,
        ]);
        $member = User::factory()->create();
        $travel = $this->createFormsRequiredTrip($contact, true);
        $assignment = $this->createTravelAssignment($travel, $member, false);

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertTrue($notification->shouldSend($member, 'mail'));
    }

    public function test_should_send_when_envelope_exists(): void
    {
        $contact = User::factory()->create([
            'docusign_access_token' => null,
        ]);
        $member = User::factory()->create();
        $travel = $this->createFormsRequiredTrip($contact, true);
        $assignment = $this->createTravelAssignment($travel, $member, true);

        $envelope = new DocuSignEnvelope();
        $envelope->signable_type = $assignment->getMorphClass();
        $envelope->signable_id = $assignment->id;
        $envelope->signed_by = $member->id;
        $envelope->envelope_id = 'D96907E7C7D945F5A4BDBA6B660C6F06';
        $envelope->save();

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertTrue($notification->shouldSend($member, 'mail'));
    }

    public function test_should_send_when_return_date_is_in_the_future(): void
    {
        $contact = User::factory()->create([
            'docusign_access_token' => null,
        ]);
        $member = User::factory()->create();
        $travel = $this->createFormsRequiredTrip($contact, false);
        $assignment = $this->createTravelAssignment($travel, $member, true);

        $notification = new TravelAssignmentReminder($assignment);

        $this->assertTrue($notification->shouldSend($member, 'mail'));
    }

    public function test_send_reminders_falls_through_to_unpaid_assignment(): void
    {
        $blockedContact = User::factory()->create([
            'docusign_access_token' => null,
        ]);
        $member = User::factory()->create();
        $blockedTrip = $this->createFormsRequiredTrip($blockedContact, true);
        $blockedAssignment = $this->createTravelAssignment($blockedTrip, $member, true);

        $unpaidContact = User::factory()->create();
        $unpaidTrip = Travel::factory()->create([
            'primary_contact_user_id' => $unpaidContact->id,
            'departure_date' => CarbonImmutable::now()->addDays(3),
            'return_date' => CarbonImmutable::now()->addDays(10),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        $unpaidAssignment = $this->createTravelAssignment($unpaidTrip, $member, false);

        $this->assertTrue($blockedAssignment->cannotReceiveDocuSignReminder());

        Queue::fake();

        (new SendReminders($member))->handle();

        Queue::assertPushed(
            SendTravelAssignmentReminder::class,
            static function (SendTravelAssignmentReminder $job) use ($unpaidAssignment): bool {
                $assignment = (new \ReflectionProperty($job, 'assignment'))->getValue($job);

                return $assignment->is($unpaidAssignment);
            }
        );
    }

    public function test_send_reminders_does_not_dispatch_when_only_blocked_docusign_assignment_exists(): void
    {
        $contact = User::factory()->create([
            'docusign_access_token' => null,
        ]);
        $member = User::factory()->create();
        $travel = $this->createFormsRequiredTrip($contact, true);
        $assignment = $this->createTravelAssignment($travel, $member, true);

        $this->assertTrue($assignment->cannotReceiveDocuSignReminder());

        Queue::fake();

        (new SendReminders($member))->handle();

        Queue::assertNotPushed(SendTravelAssignmentReminder::class);
    }

    private function createFormsRequiredTrip(User $contact, bool $past): Travel
    {
        return Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => $past ? CarbonImmutable::now()->subDays(10) : CarbonImmutable::now()->addDays(3),
            'return_date' => $past ? CarbonImmutable::now()->subDays(5) : CarbonImmutable::now()->addDays(10),
            'fee_amount' => 10,
            'status' => 'approved',
            'forms' => [
                Travel::TRAVEL_INFORMATION_FORM_KEY => true,
            ],
        ]);
    }
}
