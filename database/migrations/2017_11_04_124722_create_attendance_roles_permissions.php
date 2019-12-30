<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAttendanceRolesPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');
        Permission::firstOrCreate(['name' => 'create-attendance']);
        Permission::firstOrCreate(['name' => 'read-attendance']);
        Permission::firstOrCreate(['name' => 'read-attendance-own']);
        Permission::firstOrCreate(['name' => 'update-attendance']);
        Permission::firstOrCreate(['name' => 'delete-attendance']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo('create-attendance');
        $role->givePermissionTo('read-attendance');
        $role->givePermissionTo('read-attendance-own');
        $role->givePermissionTo('update-attendance');
        $role->givePermissionTo('delete-attendance');

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo('create-attendance');
        $role->givePermissionTo('read-attendance');
        $role->givePermissionTo('read-attendance-own');
        $role->givePermissionTo('update-attendance');
        $role->givePermissionTo('delete-attendance');

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo('create-attendance');
        $role->givePermissionTo('read-attendance');
        $role->givePermissionTo('read-attendance-own');
        $role->givePermissionTo('update-attendance');
        $role->givePermissionTo('delete-attendance');

        $role = Role::firstOrCreate(['name' => 'core']);
        $role->givePermissionTo('create-attendance');
        $role->givePermissionTo('read-attendance');
        $role->givePermissionTo('read-attendance-own');

        $role = Role::firstOrCreate(['name' => 'member']);
        $role->givePermissionTo('read-attendance-own');

        $role = Role::firstOrCreate(['name' => 'non-member']);
        $role->givePermissionTo('read-attendance-own');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('name', 'create-attendance')->delete();
        Permission::where('name', 'read-attendance')->delete();
        Permission::where('name', 'read-attendance-own')->delete();
        Permission::where('name', 'update-attendance')->delete();
        Permission::where('name', 'delete-attendance')->delete();
    }
}
