<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $p_readRP = Permission::firstOrCreate(['name' => 'read-roles-and-permissions']);
        $p_writeRP = Permission::firstOrCreate(['name' => 'write-roles-and-permissions']);

        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($p_readRP);
        $r_admin->givePermissionTo($p_writeRP);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'read-roles-and-permissions')->delete();
        Permission::where('name', 'delete-roles-and-permissions')->delete();
    }
}
