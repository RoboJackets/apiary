<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddHiddenTeamsPermission extends Migration
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

        Permission::firstOrCreate(['name' => 'read-teams-hidden']);
        Permission::firstOrCreate(['name' => 'update-teams-hidden']);
        Permission::firstOrCreate(['name' => 'update-teams-membership-own']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo('read-teams-hidden');
        $role->givePermissionTo('update-teams-hidden');
        $role->givePermissionTo('update-teams-membership-own');

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo('update-teams-membership-own');

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo('update-teams-membership-own');

        $role = Role::firstOrCreate(['name' => 'core']);
        $role->givePermissionTo('update-teams-membership-own');

        $role = Role::firstOrCreate(['name' => 'member']);
        $role->givePermissionTo('update-teams-membership-own');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'read-teams-hidden')->delete();
        Permission::where('name', 'update-teams-hidden')->delete();
        Permission::where('name', 'update-teams-membership-own')->delete();
    }
}
