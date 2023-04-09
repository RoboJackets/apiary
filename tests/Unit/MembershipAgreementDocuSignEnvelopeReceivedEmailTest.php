<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\MembershipAgreementDocuSignEnvelopeReceived;
use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\User;
use Tests\TestCase;

final class MembershipAgreementDocuSignEnvelopeReceivedEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MembershipAgreementTemplate::factory()->create();
    }

    public function testGenerateEmailForElectronicSignature(): void
    {
        $user = User::factory()->create();

        $signature = Signature::factory()->make([
            'electronic' => true,
            'user_id' => $user->id,
        ]);
        $signature->save();

        $envelope = new DocuSignEnvelope();
        $envelope->signable_type = $signature->getMorphClass();
        $envelope->signable_id = $signature->id;
        $envelope->signed_by = $user->id;
        $envelope->envelope_id = 'D96907E7C7D945F5A4BDBA6B660C6F06';
        $envelope->save();

        $mailable = new MembershipAgreementDocuSignEnvelopeReceived($envelope);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText('https://app.docusign.com/documents/details/d96907e7-c7d9-45f5-a4bd-ba6b660c6f06');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
