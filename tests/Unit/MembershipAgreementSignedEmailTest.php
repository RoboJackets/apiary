<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\MembershipAgreementSigned;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\User;
use Tests\TestCase;

class MembershipAgreementSignedEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MembershipAgreementTemplate::factory()->create();

        User::factory()->create();
    }

    public function testGenerateEmailForPaperSignature(): void
    {
        $signature = Signature::factory()->create();
        $signature->uploaded_by = User::factory()->create()->id;
        $signature->save();

        $mailable = new MembershipAgreementSigned($signature);

        $mailable->assertSeeInText($signature->user->preferred_first_name);
        $mailable->assertSeeInText('was uploaded');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testGenerateEmailForElectronicSignature(): void
    {
        $signature = Signature::factory()->create();
        $signature->electronic = true;
        $signature->save();

        $mailable = new MembershipAgreementSigned($signature);

        $mailable->assertSeeInText($signature->user->preferred_first_name);
        $mailable->assertSeeInText('electronically signed');
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
