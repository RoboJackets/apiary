<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class AddHiddenTeamsPermissionToNonMember extends Migration
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

        $role = Role::firstOrCreate(['name' => 'non-member']);
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

        $role = Role::firstOrCreate(['name' => 'non-member']);
        $role->revokePermissionTo('update-teams-membership-own');
        Permission::where('name', 'update-teams-membership-own')->delete();
    }
}
