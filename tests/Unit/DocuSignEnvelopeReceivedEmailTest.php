<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\Travel\DocuSignEnvelopeReceived;
use App\Models\DocuSignEnvelope;
use App\Models\Payment;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Tests\TestCase;

class DocuSignEnvelopeReceivedEmailTest extends TestCase
{
    public function testPaid(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel->fee_amount = 10;
        $travel->save();

        $assignment = TravelAssignment::factory()->create();

        $payment = new Payment();
        $payment->payable_type = TravelAssignment::getMorphClassStatic();
        $payment->payable_id = $assignment->id;
        $payment->amount = 10;
        $payment->method = 'square';
        $payment->receipt_url = 'https://example.com';
        $payment->save();

        $contact = User::factory()->create();

        $travel->primary_contact_user_id = $contact->id;
        $travel->save();

        $envelope = new DocuSignEnvelope();
        $envelope->signable_type = $assignment->getMorphClass();
        $envelope->signable_id = $assignment->id;
        $envelope->signed_by = $user->id;
        $envelope->envelope_id = 'D96907E7C7D945F5A4BDBA6B660C6F06';
        $envelope->save();

        $mailable = new DocuSignEnvelopeReceived($envelope);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertDontSeeInText('still need to make a $10 payment');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }

    public function testNotPaid(): void
    {
        $user = User::factory()->create();

        $travel = Travel::factory()->create();
        $travel->fee_amount = 10;
        $travel->save();

        $assignment = TravelAssignment::factory()->create();

        $contact = User::factory()->create();

        $travel->primary_contact_user_id = $contact->id;
        $travel->save();

        $envelope = new DocuSignEnvelope();
        $envelope->signable_type = $assignment->getMorphClass();
        $envelope->signable_id = $assignment->id;
        $envelope->signed_by = $user->id;
        $envelope->envelope_id = 'D96907E7C7D945F5A4BDBA6B660C6F06';
        $envelope->save();

        $mailable = new DocuSignEnvelopeReceived($envelope);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText($travel->name);
        $mailable->assertSeeInText($contact->full_name);
        $mailable->assertSeeInText('still need to make a $10 payment');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
    }
}
