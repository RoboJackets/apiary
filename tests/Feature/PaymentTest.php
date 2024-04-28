<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\TravelAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private string $DUES_PAYMENT;

    private string $TRAVEL_ASSIGNMENT;

    protected function setUp(): void
    {
        parent::setUp();
        $this->DUES_PAYMENT = DuesTransaction::getMorphClassStatic();
        $this->TRAVEL_ASSIGNMENT = TravelAssignment::getMorphClassStatic();
    }

    /**
     * Check a payment's JSON representation for accuracy.
     */
    public static function checkPaymentJson(
        User $user,
        bool $isDuesTransactionPayable,
        bool $isTravelAssignmentPayable,
        string $expectedPayableType,
        string $expectedPayableName,
        float $expectedPayableCost,
        string $expectedPaymentMethodPresentation,
        bool $expectPrivilegedRecordedByUserInfo = false,
        bool $allowUnexpectedProps = true
    ): Closure {
        return static function (AssertableJson $json) use (
            $isDuesTransactionPayable,
            $isTravelAssignmentPayable,
            $expectedPayableType,
            $expectedPayableName,
            $expectedPayableCost,
            $allowUnexpectedProps,
            $expectPrivilegedRecordedByUserInfo,
            $expectedPaymentMethodPresentation,
            $user
        ) {
            $base = $json->has('id')
                ->where('payable_type', $expectedPayableType)
                ->where('amount', static fn (string $amount) => (float) $amount === $expectedPayableCost)
                ->where('method_presentation', $expectedPaymentMethodPresentation)
                ->where('recorded_by_user.name', static fn (?string $name) => $name !== null && strlen($name) > 0);

            if ($isDuesTransactionPayable) {
                $base->where('dues_transaction.package.name', $expectedPayableName)
                    ->where('dues_transaction.user_id', $user->id);
            }

            if ($isTravelAssignmentPayable) {
                $base->where('travel_assignment.travel.name', $expectedPayableName)
                    ->where('travel_assignment.user_id', $user->id);
            }

            if ($expectPrivilegedRecordedByUserInfo) {
                $base->has('recorded_by_user.gtid');
            } else {
                $base->missing('recorded_by_user.gtid');
            }

            if ($allowUnexpectedProps) {
                $base->etc();
            }
        };
    }

    /**
     * A new user should have no payment history.
     */
    public function testNewUserHasNoPayments(): void
    {
        $user = $this->getTestUser(['non-member']);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(
            static function (AssertableJson $json): void {
                $json->where('status', 'success')
                    ->where('payments', []);
            }
        );
    }

    /**
     * A new user should have no payment history, even if they have an unpaid dues transaction.
     */
    public function testNewUserHasNoPaymentsWithUnpaidDuesTransaction(): void
    {
        $user = $this->getTestUser(['non-member']);

        $duesPackage = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackage, $user, false);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->where('payments', []);
        });
    }

    /**
     * A user who paid dues should be able to view the associated payment.
     */
    public function testUserWith1DuesPayment(): void
    {
        $user = $this->getTestUser(['non-member']);

        $duesPackage = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackage, $user, true);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $duesPayableType = $this->DUES_PAYMENT;
        $response->assertJson(
            static function (AssertableJson $json) use ($duesPayableType, $response, $user, $duesPackage): void {
                $json->where('status', 'success')
                    ->has('payments', 1)
                    ->has('payments.0', PaymentTest::checkPaymentJson(
                        $user,
                        true,
                        false,
                        $duesPayableType,
                        $duesPackage->name,
                        $duesPackage->cost,
                        Payment::$methods[$response->json('payments.0.method')]
                    ));
            }
        );
    }

    /**
     * There should only be one payment for a user with 2 dues transactions where only 1 is paid.
     */
    public function testUserWith1Paid1UnpaidDuesPayment(): void
    {
        $user = $this->getTestUser(['non-member']);

        $duesPackageEarlier = $this->createDuesPackage(CarbonImmutable::now()->subYear());
        $this->createDuesTransactionForUser($duesPackageEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $duesPackageNow = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackageNow, $user, false, [], CarbonImmutable::now());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $duesPaymentType = $this->DUES_PAYMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $duesPaymentType,
            $duesPackageEarlier,
            $response,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 1)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    true,
                    false,
                    $duesPaymentType,
                    $duesPackageEarlier->name,
                    $duesPackageEarlier->cost,
                    Payment::$methods[$response->json('payments.0.method')]
                ));
        });
    }

    /**
     * A user who paid dues twice should be able to view the associated payments.
     */
    public function testUserWith2PaidDuesTransactions(): void
    {
        $user = $this->getTestUser(['non-member']);

        $duesPackageEarlier = $this->createDuesPackage(CarbonImmutable::now()->subYear());
        $this->createDuesTransactionForUser($duesPackageEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $duesPackageNow = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackageNow, $user, true, [], CarbonImmutable::now());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $duesPaymentType = $this->DUES_PAYMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $duesPaymentType,
            $duesPackageEarlier,
            $duesPackageNow,
            $response,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 2)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    true,
                    false,
                    $duesPaymentType,
                    $duesPackageNow->name,
                    $duesPackageNow->cost,
                    Payment::$methods[$response->json('payments.0.method')]
                ))
                ->has('payments.1', PaymentTest::checkPaymentJson(
                    $user,
                    true,
                    false,
                    $duesPaymentType,
                    $duesPackageEarlier->name,
                    $duesPackageEarlier->cost,
                    Payment::$methods[$response->json('payments.1.method')]
                ));
        });
    }

    public function testUserWith1UnpaidTravelAssignment(): void
    {
        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);
        $travel = $this->createTravel(null, 10);

        $this->createTravelAssignment($travel, $user, false);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->has('payments', 0);
        });
    }

    public function testUserWith1PaidTravelAssignment(): void
    {
        Event::fake(); // Creating the travel assignment triggers an event that we don't care about / that causes
        // errors in this context

        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);
        $travel = $this->createTravel(null, 10);

        $this->createTravelAssignment($travel, $user, true);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $travelAssignmentType = $this->TRAVEL_ASSIGNMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $travelAssignmentType,
            $travel,
            $response,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 1)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travel->name,
                    $travel->fee_amount,
                    Payment::$methods[$response->json('payments.0.method')]
                ));
        });
    }

    public function testUserWith1Unpaid1PaidTravelAssignment(): void
    {
        Event::fake(); // Creating the travel assignment triggers an event that we don't care about / that causes
        // errors in this context

        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);

        $travelEarlier = $this->createTravel(CarbonImmutable::now()->subYear(), 10);
        $this->createTravelAssignment($travelEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $travelNow = $this->createTravel(null, 10);
        $this->createTravelAssignment($travelNow, $user, false, [], CarbonImmutable::now());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $travelAssignmentType = $this->TRAVEL_ASSIGNMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $travelEarlier,
            $travelAssignmentType,
            $response,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 1)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travelEarlier->name,
                    $travelEarlier->fee_amount,
                    Payment::$methods[$response->json('payments.0.method')]
                ));
        });
    }

    public function testUserWith2PaidTravelAssignments(): void
    {
        Event::fake(); // Creating the travel assignment triggers an event that we don't care about / that causes
        // errors in this context

        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);

        $travelEarlier = $this->createTravel(CarbonImmutable::now()->subYear(), 10);
        $this->createTravelAssignment($travelEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $travelNow = $this->createTravel(null, 10);
        $this->createTravelAssignment($travelNow, $user, true, [], CarbonImmutable::now());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $travelAssignmentType = $this->TRAVEL_ASSIGNMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $travelNow,
            $travelEarlier,
            $travelAssignmentType,
            $response,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 2)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travelNow->name,
                    $travelNow->fee_amount,
                    Payment::$methods[$response->json('payments.0.method')]
                ))
                ->has('payments.1', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travelEarlier->name,
                    $travelEarlier->fee_amount,
                    Payment::$methods[$response->json('payments.1.method')]
                ));
        });
    }

    public function testUserWithMixedDuesTransactionsAndTravelAssignments(): void
    {
        Event::fake(); // Creating the travel assignment triggers an event that we don't care about / that causes
        // errors in this context

        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);

        // duesPackageEarlier and travelNow are paid

        $duesPackageEarlier = $this->createDuesPackage(CarbonImmutable::now()->subMonth());
        $this->createDuesTransactionForUser(
            $duesPackageEarlier,
            $user,
            true,
            [],
            CarbonImmutable::now()->subMonth()
        );

        $duesPackageNow = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackageNow, $user, false, [], CarbonImmutable::now());

        $travelEarlier = $this->createTravel(CarbonImmutable::now()->subYear(), 10.25);
        $this->createTravelAssignment($travelEarlier, $user, false, [], CarbonImmutable::now()->subYear());

        $travelNow = $this->createTravel(CarbonImmutable::now()->subMinute(), 10.25);
        $this->createTravelAssignment($travelNow, $user, true, [], CarbonImmutable::now()->subMinute());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $duesPackageType = $this->DUES_PAYMENT;
        $travelAssignmentType = $this->TRAVEL_ASSIGNMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $duesPackageEarlier,
            $duesPackageType,
            $response,
            $travelNow,
            $travelAssignmentType,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 2)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travelNow->name,
                    $travelNow->fee_amount,
                    Payment::$methods[$response->json('payments.0.method')]
                ))
                ->has('payments.1', PaymentTest::checkPaymentJson(
                    $user,
                    true,
                    false,
                    $duesPackageType,
                    $duesPackageEarlier->name,
                    $duesPackageEarlier->cost,
                    Payment::$methods[$response->json('payments.1.method')]
                ));
        });
    }

    public function testUserWith4PaidDuesTransactionsAndTravelAssignments(): void
    {
        Event::fake(); // Creating the travel assignment triggers an event that we don't care about / that causes
        // errors in this context

        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);

        $duesPackageNow = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackageNow, $user, true, [], CarbonImmutable::now());

        $travelNow = $this->createTravel(CarbonImmutable::now()->subMinute(), 10);
        $this->createTravelAssignment($travelNow, $user, true, [], CarbonImmutable::now()->subMinute());

        $duesPackageEarlier = $this->createDuesPackage(CarbonImmutable::now()->subMonth());
        $this->createDuesTransactionForUser(
            $duesPackageEarlier,
            $user,
            true,
            [],
            CarbonImmutable::now()->subMonth()
        );

        $travelEarlier = $this->createTravel(CarbonImmutable::now()->subYear(), 10);
        $this->createTravelAssignment($travelEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $duesPackageType = $this->DUES_PAYMENT;
        $travelAssignmentType = $this->TRAVEL_ASSIGNMENT;
        $response->assertJson(static function (AssertableJson $json) use (
            $travelEarlier,
            $duesPackageNow,
            $duesPackageEarlier,
            $duesPackageType,
            $response,
            $travelNow,
            $travelAssignmentType,
            $user
        ): void {
            $json->where('status', 'success')
                ->has('payments', 4)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    true,
                    false,
                    $duesPackageType,
                    $duesPackageNow->name,
                    $duesPackageNow->cost,
                    Payment::$methods[$response->json('payments.0.method')]
                ))
                ->has('payments.1', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travelNow->name,
                    $travelNow->fee_amount,
                    Payment::$methods[$response->json('payments.1.method')]
                ))
                ->has('payments.2', PaymentTest::checkPaymentJson(
                    $user,
                    true,
                    false,
                    $duesPackageType,
                    $duesPackageEarlier->name,
                    $duesPackageEarlier->cost,
                    Payment::$methods[$response->json('payments.2.method')]
                ))
                ->has('payments.3', PaymentTest::checkPaymentJson(
                    $user,
                    false,
                    true,
                    $travelAssignmentType,
                    $travelEarlier->name,
                    $travelEarlier->fee_amount,
                    Payment::$methods[$response->json('payments.3.method')]
                ));
        });
    }

    public function testSoftDeletedDuesPaymentHidden(): void
    {
        $user = $this->getTestUser(['non-member']);

        $duesPackage = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackage, $user, true, ['deleted_at' => CarbonImmutable::now()]);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->has('payments', 0);
        });
    }

    public function testSoftDeletedTravelPaymentHidden(): void
    {
        Event::fake(); // Creating the travel assignment triggers an event that we don't care about / that causes
        // errors in this context

        // Note: A user must exist before creating a new Travel with TravelFactory
        $user = $this->getTestUser(['member']);
        $travel = $this->createTravel(null, 10);

        $this->createTravelAssignment($travel, $user, true, ['deleted_at' => CarbonImmutable::now()]);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->has('payments', 0);
        });
    }
}
