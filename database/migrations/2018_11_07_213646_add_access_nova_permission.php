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

        $permissionNova = Permission::firstOrCreate(['name' => 'access-nova']);
        $permissionHorizon = Permission::firstOrCreate(['name' => 'access-horizon']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo($permissionNova);
        $role->givePermissionTo($permissionHorizon);

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo($permissionNova);

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo($permissionNova);
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
        Permission::where('name', 'access-horizon')->delete();
    }
}
