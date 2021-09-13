<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Get the testing user.
     *
     * @param  array<string>  $roles
     */
    protected function getTestUser(array $roles, string $uid = 'apiarytesting4'): User
    {
        $user = User::where('uid', $uid)->first();
        if (null === $user) {
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

        return $user;
    }
}
