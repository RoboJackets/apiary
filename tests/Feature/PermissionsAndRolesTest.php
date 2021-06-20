<?php

declare(strict_types=1);

namespace Tests\Feature;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsAndRolesTest extends TestCase
{
    public function testPermissionsLoadedInDatabase(): void
    {
        $allPermissions = Permission::all();
        $this->assertGreaterThan(0, $allPermissions->count(), 'The permissions table appears to be ' .
            'empty, which means the database configuration for testing is not working. Are you running `composer run ' .
            'test`?');
    }

    /**
     * Ensure that the admin role has all permissions.
     */
    public function testAdminRoleHasAllPermissions(): void
    {
        $permissions = Role::where('name', 'admin')->first()->permissions;
        $allPermissions = Permission::all();
        $this->assertCount(0, $permissions->diff($allPermissions));
        $this->assertCount(0, $allPermissions->diff($permissions));
    }

    /**
     * Ensure the member and non-member roles have the same permissions.
     */
    public function testMemberAndNonMemberAreSame(): void
    {
        $nonmember = Role::where('name', 'non-member')->first()->permissions;
        $member = Role::where('name', 'member')->first()->permissions;
        $this->assertCount(0, $nonmember->diff($member));
        $this->assertCount(0, $member->diff($nonmember));
    }
}
