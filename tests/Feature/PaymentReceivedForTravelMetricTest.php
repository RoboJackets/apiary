<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Travel;
use App\Models\User;
use App\Nova\Metrics\PaymentReceivedForTravel;
use Carbon\CarbonImmutable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Tests\TestCase;

final class PaymentReceivedForTravelMetricTest extends TestCase
{
    public function test_metric_includes_charged_off_segment_between_paid_and_not_paid(): void
    {
        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);

        $this->createTravelAssignment($travel, User::factory()->create(), true);

        $chargedOff = $this->createTravelAssignment($travel, User::factory()->create(), false);
        $chargedOff->charged_off_at = now();
        $chargedOff->save();

        $this->createTravelAssignment($travel, User::factory()->create(), false);

        $result = (new PaymentReceivedForTravel($travel->id))->calculate(NovaRequest::create('/'));

        $this->assertSame(
            ['Paid', 'Charged Off', 'Not Paid'],
            array_keys($result->value)
        );
        $this->assertSame(1, (int) $result->value['Paid']);
        $this->assertSame(1, (int) $result->value['Charged Off']);
        $this->assertSame(1, (int) $result->value['Not Paid']);
    }

    public function test_paid_takes_precedence_over_charged_off(): void
    {
        $contact = User::factory()->create();
        $travel = Travel::factory()->create([
            'primary_contact_user_id' => $contact->id,
            'departure_date' => CarbonImmutable::now()->subDays(10),
            'return_date' => CarbonImmutable::now()->subDays(5),
            'fee_amount' => 10,
            'status' => 'approved',
        ]);

        $assignment = $this->createTravelAssignment($travel, User::factory()->create(), true);
        $assignment->charged_off_at = now();
        $assignment->save();

        $result = (new PaymentReceivedForTravel($travel->id))->calculate(NovaRequest::create('/'));

        $this->assertArrayHasKey('Paid', $result->value);
        $this->assertArrayNotHasKey('Charged Off', $result->value);
        $this->assertSame(1, (int) $result->value['Paid']);
    }
}
