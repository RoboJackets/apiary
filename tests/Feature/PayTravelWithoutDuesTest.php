<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\MembershipAgreementTemplateSeeder;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
final class PayTravelWithoutDuesTest extends TestCase
{
    public function test_travel_tab_hides_dues_action_when_return_date_has_passed(): void
    {
        $this->withoutMix();

        $member = $this->getTestUser(['non-member'], 'paytravel3');
        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        TravelAssignment::withoutEvents(
            static fn (): TravelAssignment => TravelAssignment::factory()->create([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ])
        );

        $response = $this->actingAs($member, 'web')->get(route('travel.index'));

        $response->assertOk();
        $response->assertDontSee('to pay dues');
        $response->assertSee('Pay the trip fee');
    }

    public function test_travel_tab_shows_dues_action_when_return_date_is_in_the_future(): void
    {
        $this->withoutMix();

        $member = $this->getTestUser(['non-member'], 'paytravel4');
        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->addDays(3),
            'return_date' => CarbonImmutable::now()->addDays(10),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        TravelAssignment::withoutEvents(
            static fn (): TravelAssignment => TravelAssignment::factory()->create([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ])
        );

        $response = $this->actingAs($member, 'web')->get(route('travel.index'));

        $response->assertOk();
        $response->assertSee('to pay dues');
    }

    public function test_inactive_user_can_start_checkout_when_return_date_has_passed(): void
    {
        $this->withoutMix();

        $member = $this->getTestUser(['non-member'], 'paytravel1');
        $this->signLatestAgreement($member);

        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        $assignment = TravelAssignment::withoutEvents(
            static fn (): TravelAssignment => TravelAssignment::factory()->create([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ])
        );

        $payment = new Payment();
        $payment->amount = 0;
        $payment->method = 'square';
        $payment->recorded_by = $member->id;
        $payment->unique_id = Payment::generateUniqueId();
        $payment->notes = 'Checkout flow started';
        $payment->url = 'https://example.com/square-checkout';
        $assignment->payment()->save($payment);

        $this->assertFalse($member->is_active);
        $this->assertTrue($travel->fresh()->return_date_has_passed);

        $response = $this->actingAs($member, 'web')->get(route('pay.travel'));

        $response->assertRedirect('https://example.com/square-checkout');
    }

    public function test_inactive_user_cannot_start_checkout_when_return_date_is_in_the_future(): void
    {
        $this->withoutMix();

        $member = $this->getTestUser(['non-member'], 'paytravel2');
        $this->signLatestAgreement($member);

        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->addDays(3),
            'return_date' => CarbonImmutable::now()->addDays(10),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);
        TravelAssignment::withoutEvents(
            static fn (): TravelAssignment => TravelAssignment::factory()->create([
                'travel_id' => $travel->id,
                'user_id' => $member->id,
            ])
        );

        $this->assertFalse($member->is_active);
        $this->assertFalse($travel->fresh()->return_date_has_passed);

        $response = $this->actingAs($member, 'web')->get(route('pay.travel'));

        $response->assertOk();
        $response->assertSee('pay dues');
        $response->assertSee($travel->name);
        $response->assertDontSee('square-checkout');
    }

    private function signLatestAgreement(User $signerUser): Signature
    {
        if (MembershipAgreementTemplate::count() === 0) {
            $this->seed(MembershipAgreementTemplateSeeder::class);
        }

        $admin = $this->getTestUser(['admin'], 'admin3');

        $signature = new Signature();
        $signature->electronic = false;
        $signature->user_id = $signerUser->id;
        $signature->uploaded_by = $admin->id;
        $signature->scanned_agreement = 'nonexistent.pdf';
        $signature->complete = true;
        $signature->membership_agreement_template_id = MembershipAgreementTemplate::first()->id;
        $signature->save();

        $envelope = new DocuSignEnvelope();
        $envelope->complete = true;
        $envelope->signed_by = $signerUser->id;
        $signature->envelope()->save($envelope);

        return $signature;
    }
}
