<?php

declare(strict_types=1);

namespace Tests\Feature;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PermissionsAndRolesTest extends TestCase
{
    public function test_permissions_loaded_in_database(): void
    {
        $allPermissions = Permission::all();
        $this->assertGreaterThan(
            0,
            $allPermissions->count(),
            'The permissions table appears to be '.
            'empty, which means the database configuration for testing is not working, or a database dump was '.
            'generatedincorrectly (see https://github.com/RoboJackets/apiary/issues/1801). Are you running `composer '.
            'run test`?'
        );
    }

    /**
     * Ensure that the admin role has all permissions.
     */
    public function test_admin_role_has_all_permissions(): void
    {
        $permissions = Role::where('name', 'admin')->first()->permissions;
        $allPermissions = Permission::where('name', '!=', 'refund-payments')
            ->where('name', '!=', 'impersonate-users')
            ->where('name', '!=', 'authenticate-with-docusign')
            ->where('name', '!=', 'update-airfare-policy')
            ->get();
        $this->assertCount(0, $permissions->diff($allPermissions));
        $this->assertCount(0, $allPermissions->diff($permissions));
    }

    /**
     * Ensure the member and non-member roles have the same permissions.
     */
    public function test_member_and_non_member_are_same(): void
    {
        $nonmember = Role::where('name', 'non-member')->first()->permissions;
        $member = Role::where('name', 'member')->first()->permissions;
        $this->assertCount(0, $nonmember->diff($member));
        $this->assertCount(0, $member->diff($nonmember));
    }
}
