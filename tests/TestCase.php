<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Get the testing user.
     *
     * @param array<string> $roles
     */
    protected function getTestUser(array $roles): User
    {
        $user = User::where('uid', 'apiarytesting4')->first();
        if (null === $user) {
            $user = new User();
            $user->create_reason = 'phpunit';
            $user->is_service_account = false;
            $user->uid = 'apiarytesting4';
            $user->gtid = 901234567;
            $user->gt_email = 'robojackets-it@lists.gatech.edu';
            $user->first_name = 'Apiary';
            $user->last_name = 'PHPUnit';
            $user->primary_affiliation = 'student';
            $user->has_ever_logged_in = true;
            $user->save();
        }

        $user->syncRoles($roles);

        return $user;
    }
}
