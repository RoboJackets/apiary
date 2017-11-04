<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddMorePermissions extends Migration
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
        Permission::firstOrCreate(['name' => 'read-users-demographics']);
        Permission::firstOrCreate(['name' => 'read-users-emergency-contact']);
        
        Permission::firstOrCreate(['name' => 'create-roles']);
        Permission::firstOrCreate(['name' => 'read-roles']);
        Permission::firstOrCreate(['name' => 'update-roles']);
        Permission::firstOrCreate(['name' => 'delete-roles']);
        
        Permission::firstOrCreate(['name' => 'create-permissions']);
        Permission::firstOrCreate(['name' => 'read-permissions']);
        Permission::firstOrCreate(['name' => 'update-permissions']);
        Permission::firstOrCreate(['name' => 'delete-permissions']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo([
            'read-users-demographics',
            'read-users-emergency-contact',
            'create-roles',
            'read-roles',
            'update-roles',
            'delete-roles',
            'create-permissions',
            'read-permissions',
            'update-permissions',
            'delete-permissions'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'read-users-demographics')->delete();
        Permission::where('name', 'read-users-emergency-contact')->delete();

        Permission::where('name', 'create-roles')->delete();
        Permission::where('name', 'read-roles')->delete();
        Permission::where('name', 'update-roles')->delete();
        Permission::where('name', 'delete-roles')->delete();

        Permission::where('name', 'create-permissions')->delete();
        Permission::where('name', 'read-permissions')->delete();
        Permission::where('name', 'update-permissions')->delete();
        Permission::where('name', 'delete-permissions')->delete();
    }
}
