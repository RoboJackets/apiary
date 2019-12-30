<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AllowAdminsToUpdateTeamsMembership extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $utm = Permission::firstOrCreate(['name' => 'update-teams-membership']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo($utm);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::where('name', 'update-teams-membership')->delete();
    }
}
