<?php

namespace Tests\Feature;

use App\Models\DuesPackage;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @param User $user
     * @param DuesPackage $duesPackage
     * @param string $expectedPaymentMethodPresentation
     * @param bool $expectPrivilegedRecordedByUserInfo
     * @param bool $allowUnexpectedProps
     * @return Closure
     */
    static function checkPaymentJson(User        $user,
                                     DuesPackage $duesPackage,
                                     string      $expectedPaymentMethodPresentation,
                                     bool        $expectPrivilegedRecordedByUserInfo = false,
                                     bool        $allowUnexpectedProps = true,
    ): Closure
    {
        return static function (AssertableJson $json) use ($allowUnexpectedProps, $expectPrivilegedRecordedByUserInfo, $expectedPaymentMethodPresentation, $user, $duesPackage) {
            $base = $json->has('id')
                ->where('amount', $duesPackage->cost)
                ->where('method_presentation', $expectedPaymentMethodPresentation)
                ->where('dues_transaction.package.name', $duesPackage->name)
                ->where('dues_transaction.user_id', $user->id)
                ->where('recorded_by_user.name', static function (?string $name) {
                    return $name !== null && strlen($name) > 0;
                });

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
     *
     * @return void
     */
    public function testNewUserHasNoPayments(): void
    {
        $user = $this->getTestUser(["non-member"]);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->where('payments', []);
        });
    }

    /**
     * A new user should have no payment history, even if they have an unpaid dues transaction.
     *
     * @return void
     */
    public function testNewUserHasNoPaymentsWithUnpaidDuesTransaction(): void
    {
        $user = $this->getTestUser(["non-member"]);

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
     *
     * @return void
     */
    public function testUserWith1DuesPayment(): void
    {
        $user = $this->getTestUser(["non-member"]);

        $duesPackage = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackage, $user, true);

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) use ($response, $user, $duesPackage): void {
            $json->where('status', 'success')
                ->has('payments', 1)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    $duesPackage,
                    Payment::$methods[$response->json('payments.0.method')],
                ));
        });
    }

    /**
     * There should only be one payment for a user with 2 dues transactions where only 1 is paid.
     *
     * @return void
     */
    public function testUserWith1Paid1UnpaidDuesPayment(): void
    {
        $user = $this->getTestUser(["non-member"]);

        $duesPackageEarlier = $this->createDuesPackage(CarbonImmutable::now()->subYear());
        $this->createDuesTransactionForUser($duesPackageEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $duesPackageNow = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackageNow, $user, false, [], CarbonImmutable::now());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($duesPackageEarlier, $duesPackageNow, $response, $user): void {
            $json->where('status', 'success')
                ->has('payments', 1)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    $duesPackageEarlier,
                    Payment::$methods[$response->json('payments.0.method')],
                ));
        });
    }

    /**
     * A user who paid dues twice should be able to view the associated payments.
     *
     * @return void
     */
    public function testUserWith2PaidDuesTransactions(): void
    {
        $user = $this->getTestUser(["non-member"]);

        $duesPackageEarlier = $this->createDuesPackage(CarbonImmutable::now()->subYear());
        $this->createDuesTransactionForUser($duesPackageEarlier, $user, true, [], CarbonImmutable::now()->subYear());

        $duesPackageNow = $this->createDuesPackage(CarbonImmutable::now());
        $this->createDuesTransactionForUser($duesPackageNow, $user, true, [], CarbonImmutable::now());

        $response = $this->actingAs($user, 'api')->get('/api/v1/payments/user/'.$user->id);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($duesPackageEarlier, $duesPackageNow, $response, $user): void {
            $json->where('status', 'success')
                ->has('payments', 2)
                ->has('payments.0', PaymentTest::checkPaymentJson(
                    $user,
                    $duesPackageNow,
                    Payment::$methods[$response->json('payments.0.method')],
                ))
                ->has('payments.1', PaymentTest::checkPaymentJson(
                    $user,
                    $duesPackageEarlier,
                    Payment::$methods[$response->json('payments.1.method')],
                ));
        });
    }
}
