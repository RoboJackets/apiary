<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\MembershipAgreementDocuSignEnvelopeReceived;
use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\User;
use Tests\TestCase;

class MembershipAgreementDocuSignEnvelopeReceivedEmailTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        MembershipAgreementTemplate::factory()->create();

        User::factory()->create();
    }

    public function testGenerateEmailForElectronicSignature(): void
    {
        $signature = Signature::factory()->create();
        $signature->electronic = true;
        $signature->save();

        $envelope = new DocuSignEnvelope();
        $envelope->signable_type = $signature->getMorphClass();
        $envelope->signable_id = $signature->id;
        $envelope->signer_ip_address = '127.0.0.1';
        $envelope->signed_by = User::sole()->id;
        $envelope->envelope_id = 'D96907E7C7D945F5A4BDBA6B660C6F06';
        $envelope->save();

        $mailable = new MembershipAgreementDocuSignEnvelopeReceived($envelope);

        $mailable->assertSeeInText($signature->user->preferred_first_name);
        $mailable->assertSeeInText('electronically signed');
        $mailable->assertSeeInText('127.0.0.1');
        $mailable->assertSeeInText('https://app.docusign.com/documents/details/d96907e7-c7d9-45f5-a4bd-ba6b660c6f06');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
