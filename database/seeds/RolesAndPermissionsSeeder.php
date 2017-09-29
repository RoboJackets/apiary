<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        Permission::create(['name' => 'create-users']);
        Permission::create(['name' => 'read-users']);
        Permission::create(['name' => 'read-users-own']);
        Permission::create(['name' => 'update-users']);
        Permission::create(['name' => 'update-users-own']);
        Permission::create(['name' => 'delete-users']);
        Permission::create(['name' => 'create-events']);
        Permission::create(['name' => 'read-events']);
        Permission::create(['name' => 'update-events']);
        Permission::create(['name' => 'update-events-own']);
        Permission::create(['name' => 'delete-events']);
        Permission::create(['name' => 'create-rsvp']);
        Permission::create(['name' => 'create-rsvp-own']);
        Permission::create(['name' => 'read-rsvps']);
        Permission::create(['name' => 'read-rsvps-own']);
        Permission::create(['name' => 'update-rsvps']);
        Permission::create(['name' => 'update-rsvps-own']);
        Permission::create(['name' => 'delete-rsvps']);
        Permission::create(['name' => 'delete-rsvp-own']);
        Permission::create(['name' => 'create-payments']);
        Permission::create(['name' => 'create-payments-own']);
        Permission::create(['name' => 'read-payments']);
        Permission::create(['name' => 'read-payments-own']);
        Permission::create(['name' => 'update-payments']);
        Permission::create(['name' => 'delete-payments']);
        Permission::create(['name' => 'create-dues-packages']);
        Permission::create(['name' => 'read-dues-packages']);
        Permission::create(['name' => 'update-dues-packages']);
        Permission::create(['name' => 'delete-dues-packages']);
        Permission::create(['name' => 'create-dues-transactions']);
        Permission::create(['name' => 'read-dues-transactions']);
        Permission::create(['name' => 'read-dues-transactions-own']);
        Permission::create(['name' => 'update-dues-transactions']);
        Permission::create(['name' => 'delete-dues-transactions']);
        Permission::create(['name' => 'create-faset-visits']);
        Permission::create(['name' => 'read-faset-visits']);
        Permission::create(['name' => 'read-faset-visits-own']);
        Permission::create(['name' => 'update-faset-visits']);
        Permission::create(['name' => 'update-faset-visits-own']);
        Permission::create(['name' => 'delete-faset-visits']);
        Permission::create(['name' => 'delete-faset-visits-own']);
        Permission::create(['name' => 'send-notifications']);

        // create roles and assign existing permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo('create-users');
        $role->givePermissionTo('read-users');
        $role->givePermissionTo('read-users-own');
        $role->givePermissionTo('update-users');
        $role->givePermissionTo('update-users-own');
        $role->givePermissionTo('delete-users');
        $role->givePermissionTo('create-events');
        $role->givePermissionTo('read-events');
        $role->givePermissionTo('update-events');
        $role->givePermissionTo('update-events-own');
        $role->givePermissionTo('delete-events');
        $role->givePermissionTo('create-rsvp');
        $role->givePermissionTo('create-rsvp-own');
        $role->givePermissionTo('read-rsvps');
        $role->givePermissionTo('read-rsvps-own');
        $role->givePermissionTo('update-rsvps');
        $role->givePermissionTo('update-rsvps-own');
        $role->givePermissionTo('delete-rsvps');
        $role->givePermissionTo('delete-rsvp-own');
        $role->givePermissionTo('create-payments');
        $role->givePermissionTo('create-payments-own');
        $role->givePermissionTo('read-payments');
        $role->givePermissionTo('read-payments-own');
        $role->givePermissionTo('update-payments');
        $role->givePermissionTo('delete-payments');
        $role->givePermissionTo('create-dues-packages');
        $role->givePermissionTo('read-dues-packages');
        $role->givePermissionTo('update-dues-packages');
        $role->givePermissionTo('delete-dues-packages');
        $role->givePermissionTo('create-dues-transactions');
        $role->givePermissionTo('read-dues-transactions');
        $role->givePermissionTo('read-dues-transactions-own');
        $role->givePermissionTo('update-dues-transactions');
        $role->givePermissionTo('delete-dues-transactions');
        $role->givePermissionTo('create-faset-visits');
        $role->givePermissionTo('read-faset-visits');
        $role->givePermissionTo('read-faset-visits-own');
        $role->givePermissionTo('update-faset-visits');
        $role->givePermissionTo('update-faset-visits-own');
        $role->givePermissionTo('delete-faset-visits');
        $role->givePermissionTo('delete-faset-visits-own');
        $role->givePermissionTo('send-notifications');

        $role = Role::create(['name' => 'officer-ii']);
        $role->givePermissionTo('read-users');
        $role->givePermissionTo('read-users-own');
        $role->givePermissionTo('update-users');
        $role->givePermissionTo('update-users-own');
        $role->givePermissionTo('create-events');
        $role->givePermissionTo('read-events');
        $role->givePermissionTo('update-events');
        $role->givePermissionTo('update-events-own');
        $role->givePermissionTo('delete-events');
        $role->givePermissionTo('create-rsvp-own');
        $role->givePermissionTo('read-rsvps');
        $role->givePermissionTo('read-rsvps-own');
        $role->givePermissionTo('update-rsvps');
        $role->givePermissionTo('update-rsvps-own');
        $role->givePermissionTo('delete-rsvps');
        $role->givePermissionTo('delete-rsvp-own');
        $role->givePermissionTo('create-payments');
        $role->givePermissionTo('read-payments');
        $role->givePermissionTo('read-payments-own');
        $role->givePermissionTo('create-dues-packages');
        $role->givePermissionTo('read-dues-packages');
        $role->givePermissionTo('update-dues-packages');
        $role->givePermissionTo('create-dues-transactions');
        $role->givePermissionTo('read-dues-transactions');
        $role->givePermissionTo('read-dues-transactions-own');
        $role->givePermissionTo('update-dues-transactions');
        $role->givePermissionTo('create-faset-visits');
        $role->givePermissionTo('read-faset-visits');
        $role->givePermissionTo('read-faset-visits-own');
        $role->givePermissionTo('update-faset-visits');
        $role->givePermissionTo('update-faset-visits-own');

        $role = Role::create(['name' => 'officer-i']);
        $role->givePermissionTo('read-users');
        $role->givePermissionTo('read-users-own');
        $role->givePermissionTo('update-users');
        $role->givePermissionTo('update-users-own');
        $role->givePermissionTo('create-events');
        $role->givePermissionTo('read-events');
        $role->givePermissionTo('update-events-own');
        $role->givePermissionTo('create-rsvp-own');
        $role->givePermissionTo('read-rsvps');
        $role->givePermissionTo('read-rsvps-own');
        $role->givePermissionTo('update-rsvps-own');
        $role->givePermissionTo('delete-rsvp-own');
        $role->givePermissionTo('create-payments');
        $role->givePermissionTo('read-payments');
        $role->givePermissionTo('read-payments-own');
        $role->givePermissionTo('read-dues-packages');
        $role->givePermissionTo('create-dues-transactions');
        $role->givePermissionTo('read-dues-transactions');
        $role->givePermissionTo('read-dues-transactions-own');
        $role->givePermissionTo('update-dues-transactions');
        $role->givePermissionTo('create-faset-visits');
        $role->givePermissionTo('read-faset-visits-own');
        $role->givePermissionTo('update-faset-visits-own');
        
        $role = Role::create(['name' => 'core']);
        $role->givePermissionTo('read-users-own');
        $role->givePermissionTo('update-users-own');
        $role->givePermissionTo('create-events');
        $role->givePermissionTo('read-events');
        $role->givePermissionTo('update-events-own');
        $role->givePermissionTo('create-rsvp-own');
        $role->givePermissionTo('read-rsvps-own');
        $role->givePermissionTo('update-rsvps-own');
        $role->givePermissionTo('delete-rsvp-own');
        $role->givePermissionTo('read-payments-own');
        $role->givePermissionTo('read-dues-packages');
        $role->givePermissionTo('create-dues-transactions');
        $role->givePermissionTo('read-dues-transactions-own');
        $role->givePermissionTo('create-faset-visits');
        $role->givePermissionTo('read-faset-visits-own');
        $role->givePermissionTo('update-faset-visits-own');
        
        $role = Role::create(['name' => 'member']);
        $role->givePermissionTo('read-users-own');
        $role->givePermissionTo('update-users-own');
        $role->givePermissionTo('read-events');
        $role->givePermissionTo('create-rsvp-own');
        $role->givePermissionTo('read-rsvps-own');
        $role->givePermissionTo('update-rsvps-own');
        $role->givePermissionTo('delete-rsvp-own');
        $role->givePermissionTo('read-payments-own');
        $role->givePermissionTo('read-dues-packages');
        $role->givePermissionTo('create-dues-transactions');
        $role->givePermissionTo('read-dues-transactions-own');
        $role->givePermissionTo('read-faset-visits-own');
        $role->givePermissionTo('update-faset-visits-own');
    }
}
