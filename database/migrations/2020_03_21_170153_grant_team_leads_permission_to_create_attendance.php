<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GrantTeamLeadsPermissionToCreateAttendance extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $ca = Permission::firstOrCreate(['name' => 'create-attendance']);

        $role = Role::firstOrCreate(['name' => 'team-lead']);
        $role->givePermissionTo($ca);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do I really need to write this?
    }
}
