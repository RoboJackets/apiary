<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class CreateInitialRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        Permission::firstOrCreate(['name' => 'create-users']);
        Permission::firstOrCreate(['name' => 'read-users']);
        Permission::firstOrCreate(['name' => 'read-users-own']);
        Permission::firstOrCreate(['name' => 'update-users']);
        Permission::firstOrCreate(['name' => 'update-users-own']);
        Permission::firstOrCreate(['name' => 'delete-users']);
        Permission::firstOrCreate(['name' => 'create-events']);
        Permission::firstOrCreate(['name' => 'read-events']);
        Permission::firstOrCreate(['name' => 'update-events']);
        Permission::firstOrCreate(['name' => 'update-events-own']);
        Permission::firstOrCreate(['name' => 'delete-events']);
        Permission::firstOrCreate(['name' => 'create-rsvp']);
        Permission::firstOrCreate(['name' => 'create-rsvp-own']);
        Permission::firstOrCreate(['name' => 'read-rsvps']);
        Permission::firstOrCreate(['name' => 'read-rsvps-own']);
        Permission::firstOrCreate(['name' => 'update-rsvps']);
        Permission::firstOrCreate(['name' => 'update-rsvps-own']);
        Permission::firstOrCreate(['name' => 'delete-rsvps']);
        Permission::firstOrCreate(['name' => 'delete-rsvp-own']);
        Permission::firstOrCreate(['name' => 'create-payments']);
        Permission::firstOrCreate(['name' => 'create-payments-own']);
        Permission::firstOrCreate(['name' => 'read-payments']);
        Permission::firstOrCreate(['name' => 'read-payments-own']);
        Permission::firstOrCreate(['name' => 'update-payments']);
        Permission::firstOrCreate(['name' => 'delete-payments']);
        Permission::firstOrCreate(['name' => 'create-dues-packages']);
        Permission::firstOrCreate(['name' => 'read-dues-packages']);
        Permission::firstOrCreate(['name' => 'update-dues-packages']);
        Permission::firstOrCreate(['name' => 'delete-dues-packages']);
        Permission::firstOrCreate(['name' => 'create-dues-transactions']);
        Permission::firstOrCreate(['name' => 'read-dues-transactions']);
        Permission::firstOrCreate(['name' => 'read-dues-transactions-own']);
        Permission::firstOrCreate(['name' => 'update-dues-transactions']);
        Permission::firstOrCreate(['name' => 'delete-dues-transactions']);
        Permission::firstOrCreate(['name' => 'create-faset-visits']);
        Permission::firstOrCreate(['name' => 'read-faset-visits']);
        Permission::firstOrCreate(['name' => 'read-faset-visits-own']);
        Permission::firstOrCreate(['name' => 'update-faset-visits']);
        Permission::firstOrCreate(['name' => 'update-faset-visits-own']);
        Permission::firstOrCreate(['name' => 'delete-faset-visits']);
        Permission::firstOrCreate(['name' => 'delete-faset-visits-own']);
        Permission::firstOrCreate(['name' => 'send-notifications']);

        // create roles and assign existing permissions
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions([
            'create-users',
            'read-users',
            'read-users-own',
            'update-users',
            'update-users-own',
            'delete-users',
            'create-events',
            'read-events',
            'update-events',
            'update-events-own',
            'delete-events',
            'create-rsvp',
            'create-rsvp-own',
            'read-rsvps',
            'read-rsvps-own',
            'update-rsvps',
            'update-rsvps-own',
            'delete-rsvps',
            'delete-rsvp-own',
            'create-payments',
            'create-payments-own',
            'read-payments',
            'read-payments-own',
            'update-payments',
            'delete-payments',
            'create-dues-packages',
            'read-dues-packages',
            'update-dues-packages',
            'delete-dues-packages',
            'create-dues-transactions',
            'read-dues-transactions',
            'read-dues-transactions-own',
            'update-dues-transactions',
            'delete-dues-transactions',
            'create-faset-visits',
            'read-faset-visits',
            'read-faset-visits-own',
            'update-faset-visits',
            'update-faset-visits-own',
            'delete-faset-visits',
            'delete-faset-visits-own',
            'send-notifications',
        ]);

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->syncPermissions([
            'read-users',
            'read-users-own',
            'update-users',
            'update-users-own',
            'create-events',
            'read-events',
            'update-events',
            'update-events-own',
            'delete-events',
            'create-rsvp-own',
            'read-rsvps',
            'read-rsvps-own',
            'update-rsvps',
            'update-rsvps-own',
            'delete-rsvps',
            'delete-rsvp-own',
            'create-payments',
            'read-payments',
            'read-payments-own',
            'create-dues-packages',
            'read-dues-packages',
            'update-dues-packages',
            'create-dues-transactions',
            'read-dues-transactions',
            'read-dues-transactions-own',
            'update-dues-transactions',
            'create-faset-visits',
            'read-faset-visits',
            'read-faset-visits-own',
            'update-faset-visits',
            'update-faset-visits-own',
        ]);

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->syncPermissions([
            'read-users',
            'read-users-own',
            'update-users',
            'update-users-own',
            'create-events',
            'read-events',
            'update-events-own',
            'create-rsvp-own',
            'read-rsvps',
            'read-rsvps-own',
            'update-rsvps-own',
            'delete-rsvp-own',
            'create-payments',
            'read-payments',
            'read-payments-own',
            'read-dues-packages',
            'create-dues-transactions',
            'read-dues-transactions',
            'read-dues-transactions-own',
            'update-dues-transactions',
            'create-faset-visits',
            'read-faset-visits-own',
            'update-faset-visits-own',
        ]);

        $role = Role::firstOrCreate(['name' => 'core']);
        $role->syncPermissions([
            'read-users-own',
            'update-users-own',
            'create-events',
            'read-events',
            'update-events-own',
            'create-rsvp-own',
            'read-rsvps-own',
            'update-rsvps-own',
            'delete-rsvp-own',
            'read-payments-own',
            'read-dues-packages',
            'create-dues-transactions',
            'read-dues-transactions-own',
            'create-faset-visits',
            'read-faset-visits-own',
            'update-faset-visits-own',
        ]);

        $role = Role::firstOrCreate(['name' => 'member']);
        $role->syncPermissions([
            'read-users-own',
            'update-users-own',
            'read-events',
            'create-rsvp-own',
            'read-rsvps-own',
            'update-rsvps-own',
            'delete-rsvp-own',
            'read-payments-own',
            'read-dues-packages',
            'create-dues-transactions',
            'read-dues-transactions-own',
            'read-faset-visits-own',
            'update-faset-visits-own',
        ]);

        $role = Role::firstOrCreate(['name' => 'non-member']);
        $role->syncPermissions([
            'read-users-own',
            'update-users-own',
            'read-events',
            'create-rsvp-own',
            'read-rsvps-own',
            'update-rsvps-own',
            'delete-rsvp-own',
            'read-payments-own',
            'read-dues-packages',
            'create-dues-transactions',
            'read-dues-transactions-own',
            'read-faset-visits-own',
            'update-faset-visits-own',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $roles = ['admin', 'officer-i', 'officer-ii', 'core', 'member', 'non-member'];
        foreach ($roles as $role) {
            $dbRole = Role::where('name', $role)->first();
            $dbRole->delete();
        }
    }
}
