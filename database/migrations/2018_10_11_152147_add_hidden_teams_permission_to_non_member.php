<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddHiddenTeamsPermissionToNonMember extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Permission::firstOrCreate(['name' => 'update-teams-membership-own']);

        $role = Role::firstOrCreate(['name' => 'non-member']);
        $role->givePermissionTo('update-teams-membership-own');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $role = Role::firstOrCreate(['name' => 'non-member']);
        $role->revokePermissionTo('update-teams-membership-own');
        Permission::where('name', 'update-teams-membership-own')->delete();
    }
}
