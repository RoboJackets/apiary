<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class OnlyAdminsCanUpdateTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $ut = Permission::firstOrCreate(['name' => 'update-teams']);

        $role = Role::firstOrCreate(['name' => 'project-manager']);
        $role->revokePermissionTo($ut);

        $role = Role::firstOrCreate(['name' => 'officer']);
        $role->revokePermissionTo($ut);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $ut = Permission::firstOrCreate(['name' => 'update-teams']);

        $role = Role::firstOrCreate(['name' => 'project-manager']);
        $role->givePermissionTo($ut);

        $role = Role::firstOrCreate(['name' => 'officer']);
        $role->givePermissionTo($ut);
    }
}
