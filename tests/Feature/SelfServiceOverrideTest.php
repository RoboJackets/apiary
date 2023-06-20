<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\MembershipAgreementTemplateSeeder;
use Database\Seeders\TeamsSeeder;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class SelfServiceOverrideTest extends TestCase
{
    /**
     * Shortcut to create a (optionally signed/completed) membership agreement signature for a given test user.
     */
    private function createMembershipAgreementSignature(User $signer_user, bool $completed): Signature
    {
        if (MembershipAgreementTemplate::count() === 0) {
            $this->seed(MembershipAgreementTemplateSeeder::class);
        }

        $admin = $this->getTestUser(['admin'], 'admin3');

        $signature = new Signature();
        $signature->electronic = false;
        $signature->user_id = $signer_user->id;
        $signature->uploaded_by = $admin->id;
        $signature->scanned_agreement = 'nonexistent.pdf';
        $signature->complete = $completed;
        $signature->membership_agreement_template_id = MembershipAgreementTemplate::first()->id;
        $signature->save();

        $envelope = new DocuSignEnvelope();
        $envelope->complete = true;
        $envelope->signed_by = $signer_user->id;
        $signature->envelope()->save($envelope);

        return $signature;
    }

    /**
     * Tests override eligibility when conditions are all true and only tasks need to be completed.
     */
    public function testOverrideEligibilityTasks(): void
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        TestCase::createDuesPackage(CarbonImmutable::now());

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertTrue(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Attend a team meeting',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Sign the membership agreement',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        $team->self_service_override_eligible = true;
        $team->save();
        Attendance::withoutEvents(static function () use ($team, $user): void {
            Attendance::create([
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
            ]);
        });

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertTrue(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Sign the membership agreement',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );

        $this->createMembershipAgreementSignature($user, true);

        $this->assertTrue(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );
    }

    /**
     * Tests override eligibility when conditions are all true and only tasks need to be completed.
     */
    public function testOverrideEligibilityTasksWithIneligibleTeam(): void
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        TestCase::createDuesPackage(CarbonImmutable::now());

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertTrue(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Attend a team meeting',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Sign the membership agreement',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        Attendance::withoutEvents(static function () use ($team, $user): void {
            Attendance::create([
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
            ]);
        });

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertTrue(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Attend a team meeting',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Sign the membership agreement',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );

        $this->createMembershipAgreementSignature($user, true);

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Attend a team meeting',
            $user->self_service_override_eligibility->getRemainingTasks(),
            (string) $user->self_service_override_eligibility
        );
    }

    /**
     * Testing instances where a user would not be eligible due to existing override.
     */
    public function testUserWithOverrideNotEligibleForSelfServiceOverride(): void
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        TestCase::createDuesPackage(CarbonImmutable::now());

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        Attendance::withoutEvents(static function () use ($team, $user): void {
            Attendance::create([
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
            ]);
        });

        $this->createMembershipAgreementSignature($user, true);
        $admin = $this->getTestUser(['admin'], 'admin3');

        $user->access_override_by_id = $admin->id;
        $user->access_override_until = CarbonImmutable::now()->subMonth();

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertFalse(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Must have no previous access override',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
    }

    /**
     * Testing instances where a user would not be eligible due to previous dues.
     */
    public function testUserWithActivePaidDuesNotEligible(): void
    {
        $user = $this->getTestUser(['non-member']);
        $dues_package = TestCase::createDuesPackage(CarbonImmutable::now());
        TestCase::createDuesTransactionForUser($dues_package, $user, true);

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertFalse(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Access must not be active',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Must have no prior dues payments',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
    }

    /**
     * Testing instances where a user would not be eligible due to no future dues package.
     */
    public function testNoFutureDuesPackage(): void
    {
        $user = $this->getTestUser(['non-member']);

        // No dues packages at all
        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertFalse(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );

        // Only dues packages in the past exist
        TestCase::createDuesPackage(CarbonImmutable::now()->subYear());

        $this->assertFalse(
            $user->self_service_override_eligibility->eligible,
            (string) $user->self_service_override_eligibility
        );
        $this->assertFalse(
            $user->self_service_override_eligibility->user_rectifiable,
            (string) $user->self_service_override_eligibility
        );
        $this->assertContains(
            'Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );

        // A future dues package exists
        // Only dues packages in the past exist
        TestCase::createDuesPackage(CarbonImmutable::now()->addYear());
        $this->assertNotContains(
            'Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
    }
}
