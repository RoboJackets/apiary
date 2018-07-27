<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class CreateUpdateTeamMembershipOwnPermission extends Migration
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

        Permission::firstOrCreate(['name' => 'update-teams-membership-own']);

        $role = Role::firstOrCreate(['name' => 'admin']);
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

        Permission::where('name', 'update-teams-membership-own')->delete();
    }
}
