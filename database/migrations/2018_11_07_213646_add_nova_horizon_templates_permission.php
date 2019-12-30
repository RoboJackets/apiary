<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddNovaHorizonTemplatesPermission extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $permissionNova = Permission::firstOrCreate(['name' => 'access-nova']);
        $permissionHorizon = Permission::firstOrCreate(['name' => 'access-horizon']);
        $permissionManageTemplates = Permission::firstOrCreate(['name' => 'manage-notification-templates']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo($permissionNova);
        $role->givePermissionTo($permissionHorizon);
        $role->givePermissionTo($permissionManageTemplates);

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo($permissionNova);

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo($permissionNova);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'access-nova')->delete();
        Permission::where('name', 'access-horizon')->delete();
        Permission::where('name', 'manage-notification-templates')->delete();
    }
}
