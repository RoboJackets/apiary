<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AllowAdminsToUpdateTeamsMembership extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $utm = Permission::firstOrCreate(['name' => 'update-teams-membership']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo($utm);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::where('name', 'update-teams-membership')->delete();
    }
}
