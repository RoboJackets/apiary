<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\MembershipAgreementTemplate;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\MembershipAgreementTemplateSeeder;
use Database\Seeders\TeamsSeeder;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SelfServiceOverrideTest extends TestCase
{
    /**
     * Shortcut to create a dummy dues package.
     *
     * @param  CarbonImmutable|null  $base_date  Date around which the dues package's validity periods will be defined
     * @return DuesPackage
     */
    public function createDuesPackage(?CarbonImmutable $base_date): DuesPackage
    {
        if (null === $base_date) {
            $base_date = CarbonImmutable::now();
        }

        $fy = FiscalYear::firstOrCreate(['ending_year' => $base_date->year]);

        $pkg = DuesPackage::factory()->create([
            'fiscal_year_id' => $fy->id,
            'effective_start' => $base_date->subMonth(),
            'effective_end' => $base_date->addMonth(),
            'access_start' => $base_date->subMonth(),
            'access_end' => $base_date->addMonth(),
            'available_for_purchase' => true,
            'restricted_to_students' => false,
        ]);


        return $pkg;
    }

    /**
     * Shortcut to create a dummy dues transaction (and optionally, payment) for a given test user.
     *
     * @param  DuesPackage  $dues_package
     * @param  User  $user
     * @param  bool  $paid
     * @return DuesTransaction
     */
    public function createDuesTransactionForUser(DuesPackage $dues_package, User $user, bool $paid): DuesTransaction
    {
        $dues_transaction = DuesTransaction::factory()->create([
            'dues_package_id' => $dues_package->id,
            'user_id' => $user->id,
        ]);

        if ($paid) {
            Payment::factory()->create([
                'payable_type' => DuesTransaction::getMorphClassStatic(),
                'payable_id' => $dues_transaction->id,
                'amount' => $dues_package->cost,
                'notes' => '',
            ]);
        }

        return $dues_transaction;
    }

    /**
     * Shortcut to create a (optionally signed/completed) membership agreement signature for a given test user.
     *
     * @param  User  $signer_user
     * @param  bool  $completed
     * @return Signature
     */
    public function createMembershipAgreementSignature(User $signer_user, bool $completed): Signature
    {
        if (0 === MembershipAgreementTemplate::count()) {
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

        return $signature;
    }

    /**
     * Tests override eligibility when conditions are all true and only tasks need to be completed.
     *
     * @return void
     */
    public function testOverrideEligibiltyTasks(): void
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        $this->createDuesPackage(CarbonImmutable::now());

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
        Attendance::create([
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
        ]);

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

    public function testUserWithOverrideNotEligibleForSelfServiceOverride()
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        $this->createDuesPackage(CarbonImmutable::now());

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        Attendance::create([
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
        ]);

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

    public function testUserWithActivePaidDuesNotEligible()
    {
        $user = $this->getTestUser(['non-member']);
        $dues_package = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($dues_package, $user, true);

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

    public function testNoFutureDuesPackage()
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
        $this->createDuesPackage(CarbonImmutable::now()->subYear());

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
        $this->createDuesPackage(CarbonImmutable::now()->addYear());
        $this->assertNotContains(
            'Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            (string) $user->self_service_override_eligibility
        );
    }
}
