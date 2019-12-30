<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OnlyAdminsCanUpdateTeams extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $ut = Permission::firstOrCreate(['name' => 'update-teams']);

        $role = Role::firstOrCreate(['name' => 'project-manager']);
        $role->givePermissionTo($ut);

        $role = Role::firstOrCreate(['name' => 'officer']);
        $role->givePermissionTo($ut);
    }
}
