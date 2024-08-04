<?php

declare(strict_types=1);

namespace Tests;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\Payment;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Faker\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Shortcut to create a dummy dues package.
     *
     * @param  CarbonImmutable|null  $base_date  Date around which the dues package's validity periods will be defined
     */
    public static function createDuesPackage(?CarbonImmutable $base_date): DuesPackage
    {
        if ($base_date === null) {
            $base_date = CarbonImmutable::now();
        }

        $fy = FiscalYear::firstOrCreate(['ending_year' => $base_date->year]);

        return DuesPackage::factory()->create([
            'fiscal_year_id' => $fy->id,
            'effective_start' => $base_date->subMonth(),
            'effective_end' => $base_date->addMonth(),
            'access_start' => $base_date->subMonth(),
            'access_end' => $base_date->addMonth(),
            'available_for_purchase' => true,
            'restricted_to_students' => false,
        ]);
    }

    /**
     * Shortcut to create a dummy dues transaction (and optionally, payment) for a given test user.
     */
    public static function createDuesTransactionForUser(
        DuesPackage $dues_package,
        User $user,
        bool $paid,
        array $payment_attrs = [],
        ?CarbonImmutable $createdAt = null
    ): DuesTransaction {
        $now = CarbonImmutable::now();
        $dues_transaction = DuesTransaction::factory()->create(
            [
                'dues_package_id' => $dues_package->id,
                'user_id' => $user->id,
                'created_at' => $createdAt ?? $now,
                'updated_at' => $createdAt ?? $now,
            ]
        );

        if ($paid) {
            $payment = Payment::factory()->create(array_merge([
                'payable_type' => DuesTransaction::getMorphClassStatic(),
                'payable_id' => $dues_transaction->id,
                'amount' => $dues_package->cost,
                'notes' => '',
                'created_at' => $createdAt ?? $now,
                'updated_at' => $createdAt ?? $now,
            ], $payment_attrs));

            // This ensures that updated_at isn't set to the present time post-creation for some reason
            $payment->updated_at = $createdAt;
            $payment->save();
        }

        return $dues_transaction;
    }

    public static function createTravel(?CarbonImmutable $base_date, int|float $fee_amount = 0): Travel
    {
        if ($base_date === null) {
            $base_date = CarbonImmutable::now();
        }

        return Travel::factory()->create([
            'departure_date' => $base_date->subDays(3),
            'return_date' => $base_date->addDays(3),
            'fee_amount' => $fee_amount,
        ]);
    }

    public static function createTravelAssignment(
        Travel $travel,
        User $user,
        bool $paid,
        array $payment_attrs = [],
        ?CarbonImmutable $createdAt = null
    ): TravelAssignment|Collection|Model {
        $now = CarbonImmutable::now();

        $travel_assignment = TravelAssignment::withoutEvents(
            static fn (): TravelAssignment => TravelAssignment::factory()->create([
                'travel_id' => $travel->id,
                'user_id' => $user->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])
        );

        if ($paid) {
            $payment = Payment::factory()->create(array_merge([
                'payable_type' => TravelAssignment::getMorphClassStatic(),
                'payable_id' => $travel_assignment->id,
                'amount' => $travel->fee_amount,
                'notes' => '',
                'created_at' => $createdAt ?? $now,
                'updated_at' => $createdAt ?? $now,
            ], $payment_attrs));

            // This ensures that updated_at isn't set to the present time post-creation for some reason
            $payment->updated_at = $createdAt;
            $payment->save();
        }

        return $travel_assignment;
    }

    /**
     * Get the testing user.
     *
     * @param  array<string>  $roles
     */
    protected function getTestUser(array $roles, string $uid = 'apiarytesting4'): User
    {
        $user = User::where('uid', $uid)->first();
        if ($user === null) {
            $faker = Factory::create();
            $user = new User();
            $user->create_reason = 'phpunit';
            $user->is_service_account = false;
            $user->uid = $uid;
            $user->gtid = $faker->unique()->numberBetween(901000000, 909999999);
            $user->gt_email = $faker->unique()->companyEmail();
            $user->first_name = $faker->unique()->firstName();
            $user->last_name = 'PHPUnit';
            $user->primary_affiliation = 'student';
            $user->has_ever_logged_in = true;
            $user->save();
        }

        $user->syncRoles($roles);
        $user->refresh();

        return $user;
    }
}
