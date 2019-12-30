<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateTeamRolesPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::firstOrCreate(['name' => 'create-teams']);
        Permission::firstOrCreate(['name' => 'read-teams']);
        Permission::firstOrCreate(['name' => 'update-teams']);
        Permission::firstOrCreate(['name' => 'delete-teams']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo('create-teams');
        $role->givePermissionTo('read-teams');
        $role->givePermissionTo('update-teams');
        $role->givePermissionTo('delete-teams');

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo('create-teams');
        $role->givePermissionTo('read-teams');
        $role->givePermissionTo('update-teams');

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo('create-teams');
        $role->givePermissionTo('read-teams');
        $role->givePermissionTo('update-teams');

        $role = Role::firstOrCreate(['name' => 'core']);
        $role->givePermissionTo('create-teams');
        $role->givePermissionTo('read-teams');

        $role = Role::firstOrCreate(['name' => 'member']);
        $role->givePermissionTo('read-teams');

        $role = Role::firstOrCreate(['name' => 'non-member']);
        $role->givePermissionTo('read-teams');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'create-teams')->delete();
        Permission::where('name', 'read-teams')->delete();
        Permission::where('name', 'update-teams')->delete();
        Permission::where('name', 'delete-teams')->delete();
    }
}
