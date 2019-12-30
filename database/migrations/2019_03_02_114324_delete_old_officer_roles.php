<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DeleteOldOfficerRoles extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::where('name', 'officer-i')->delete();
        Role::where('name', 'officer-ii')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNova = Permission::firstOrCreate(['name' => 'access-nova']);

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo($permissionNova);

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo($permissionNova);
    }
}
