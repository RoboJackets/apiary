<?php

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
    public function create_dues_package(?CarbonImmutable $base_date, int $cost = 10)
    {
        if (is_null($base_date)) {
            $base_date = CarbonImmutable::now();
        }

        if (FiscalYear::count() == 0) {
            $fy = FiscalYear::create([
                'ending_year' => $base_date->year,
            ]);
        } else {
            $fy = FiscalYear::first();
        }

        $pkg = DuesPackage::create([
            'fiscal_year_id' => $fy->id,
            'effective_start' => $base_date->subMonth(),
            'effective_end' => $base_date->addMonth(),
            'access_start' => $base_date->subMonth(),
            'access_end' => $base_date->addMonth(),
            'cost' => $cost,
            'available_for_purchase' => true,
            'name' => 'Test dues package - ' . bin2hex(openssl_random_pseudo_bytes(4)),
            'restricted_to_students' => false,
        ]);

        return $pkg;
    }

    public function create_dues_transaction_for_user(DuesPackage $dues_package, User $user, bool $paid): DuesTransaction
    {
        $dues_transaction = DuesTransaction::create([
            "dues_package_id" => $dues_package->id,
            "user_id" => $user->id,
        ]);

        if ($paid) {
            Payment::create([
                "payable_type" => "dues-transaction",
                "payable_id" => $dues_transaction->id,
                "amount" => $dues_package->cost,
                "processing_fee" => 0,
                "method" => "cash",
            ]);
        }

        return $dues_transaction;
    }

    public function create_membership_agreement_signature(User $signer_user, bool $completed): Signature
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

        return $signature;
    }

    /**
     * Tests override eligibility when conditions are all true and only tasks need to be completed.
     *
     * @return void
     */
    public function test_override_eligibilty_tasks(): void
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        $this->create_dues_package(CarbonImmutable::now());

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertTrue($user->self_service_override_eligibility->user_rectifiable, $user->self_service_override_eligibility);
        $this->assertContains('None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains('Attend a team meeting',
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);
        $this->assertContains('Sign the membership agreement',
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        Attendance::create([
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
        ]);

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertTrue($user->self_service_override_eligibility->user_rectifiable, $user->self_service_override_eligibility);
        $this->assertContains('None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains('Sign the membership agreement',
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);

        $this->create_membership_agreement_signature($user, true);

        $this->assertTrue($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertContains('None',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains('None',
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);
    }

    public function test_user_with_override_not_eligible_for_self_service_override()
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);
        $this->create_dues_package(CarbonImmutable::now());

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        Attendance::create([
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
        ]);

        $this->create_membership_agreement_signature($user, true);
        $admin = $this->getTestUser(['admin'], 'admin3');

        $user->access_override_by_id = $admin->id;
        $user->access_override_until = CarbonImmutable::now()->subMonth();

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertFalse($user->self_service_override_eligibility->user_rectifiable, $user->self_service_override_eligibility);
        $this->assertContains('Must have no previous access override',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
    }

    public function test_user_with_active_paid_dues_not_eligible() {
        $user = $this->getTestUser(['non-member']);
        $dues_package = $this->create_dues_package(CarbonImmutable::now());
        $this->create_dues_transaction_for_user($dues_package, $user, true);

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertFalse($user->self_service_override_eligibility->user_rectifiable, $user->self_service_override_eligibility);
        $this->assertContains('Access must not be active',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains('Must have no prior dues payments',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
    }

    public function test_no_future_dues_package() {
        $user = $this->getTestUser(['non-member']);

        // No dues packages at all
        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertFalse($user->self_service_override_eligibility->user_rectifiable, $user->self_service_override_eligibility);
        $this->assertContains('Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);

        // Only dues packages in the past exist
        $this->create_dues_package(CarbonImmutable::now()->subYear());

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertFalse($user->self_service_override_eligibility->user_rectifiable, $user->self_service_override_eligibility);
        $this->assertContains('Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);

        // A future dues package exists
        // Only dues packages in the past exist
        $this->create_dues_package(CarbonImmutable::now()->addYear());
        $this->assertNotContains('Future dues package must exist',
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
    }
}
