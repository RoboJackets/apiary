<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class AddAccessNovaPermission extends Migration
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

        $permission = Permission::firstOrCreate(['name' => 'access-nova']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo($permission);

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo($permission);

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo($permission);
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

        Permission::where('name', 'access-nova')->delete();
    }
}
