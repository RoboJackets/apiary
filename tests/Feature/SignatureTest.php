<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\MembershipAgreementSigned as Mailable;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Notifications\MembershipAgreementSigned;
use Database\Seeders\MembershipAgreementTemplateSeeder;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SignatureTest extends TestCase
{
    /**
     * Test the notification for uploaded scans.
     */
    public function testAgreementUploadedEmail(): void
    {
        Notification::fake();

        $this->seed(MembershipAgreementTemplateSeeder::class);

        $user = $this->getTestUser(['member']);
        $admin = $this->getTestUser(['admin'], 'admin3');

        $signature = new Signature;
        $signature->electronic = false;
        $signature->user_id = $user->id;
        $signature->uploaded_by = $admin->id;
        $signature->scanned_agreement = 'nonexistent.pdf';
        $signature->complete = false;
        $signature->membership_agreement_template_id = MembershipAgreementTemplate::first()->id;

        $signature->save();
        $signature->complete = true;
        // Force an update
        $signature->save();

        Notification::assertSentTo([$user],
            static function (MembershipAgreementSigned $notif, $channels) use ($user, $admin): bool {
                $mailable =  $notif->toMail($user);

                $mailable->assertSeeInText('signed membership agreement');
                $mailable->assertSeeInText('was uploaded');
                $mailable->assertSeeInText('by '.$admin->first_name.' '.$admin->last_name);
                $mailable->assertDontSeeInText('electronically signed');

                $cc = config('services.membership_agreement_archive_email');

                return $mailable->hasTo($user->gt_email) && $mailable->hasCc($cc);
            }
        );
    }
}
