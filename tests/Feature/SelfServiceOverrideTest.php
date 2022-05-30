<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DuesPackage;
use App\Models\FiscalYear;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\MembershipAgreementTemplateSeeder;
use Database\Seeders\TeamsSeeder;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SelfServiceOverrideTest extends TestCase
{
    public function create_dues_package() {
        $fy = FiscalYear::create([
            "ending_year" => Carbon::now()->year,
        ]);

        $pkg = DuesPackage::create([
            "fiscal_year_id" => $fy->id,
            "effective_start" => Carbon::now()->subMonth(),
            "effective_end" => Carbon::now()->addMonth(),
            "access_start" => Carbon::now()->subMonth(),
            "access_end" => Carbon::now()->addMonth(),
            "cost" => 10,
            "available_for_purchase" => true,
            "name" => "Test dues package",
            "restricted_to_students" => false,
        ]);

        return $pkg;
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
     * A basic feature test example.
     *
     * @return void
     */
    public function test_override_eligibilty_tasks()
    {
        Notification::fake();

        $user = $this->getTestUser(['non-member']);

        $dues_package = $this->create_dues_package();

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertContains("None",
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains("Attend a team meeting",
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);
        $this->assertContains("Sign the membership agreement",
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);

        $this->seed(TeamsSeeder::class);
        $team = Team::first();
        Attendance::create([
            "attendable_type" => "team",
            "attendable_id" => $team->id,
            "gtid" => $user->gtid,
        ]);

        $this->assertFalse($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertContains("None",
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains("Sign the membership agreement",
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);

        $this->create_membership_agreement_signature($user, true);

        $this->assertTrue($user->self_service_override_eligibility->eligible, $user->self_service_override_eligibility);
        $this->assertContains("None",
            $user->self_service_override_eligibility->getUnmetConditions(),
            $user->self_service_override_eligibility);
        $this->assertContains("None",
            $user->self_service_override_eligibility->getRemainingTasks(),
            $user->self_service_override_eligibility);
    }
}
