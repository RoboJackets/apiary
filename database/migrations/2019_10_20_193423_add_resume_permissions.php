<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class AddResumePermissions extends Migration
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

        $p_readResume = Permission::firstOrCreate(['name' => 'read-users-resume']);
        $p_writeResume = Permission::firstOrCreate(['name' => 'update-users-resume']);
        $p_deleteResume = Permission::firstOrCreate(['name' => 'delete-users-resume']);

        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($p_readResume);
        $r_admin->givePermissionTo($p_writeResume);
        $r_admin->givePermissionTo($p_deleteResume);
        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_officer->givePermissionTo($p_readResume);
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
        Permission::where('name', 'read-users-resume')->delete();
        Permission::where('name', 'update-users-resume')->delete();
        Permission::where('name', 'delete-users-resume')->delete();
    }
}
