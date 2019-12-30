<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddResumePermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $p_readResume = Permission::firstOrCreate(['name' => 'read-users-resume']);
        $p_writeResume = Permission::firstOrCreate(['name' => 'update-users-resume']);
        $p_deleteResume = Permission::firstOrCreate(['name' => 'delete-users-resume']);

        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($p_readResume);
        $r_admin->givePermissionTo($p_writeResume);
        $r_admin->givePermissionTo($p_deleteResume);
        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_officer->givePermissionTo($p_readResume);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'read-users-resume')->delete();
        Permission::where('name', 'update-users-resume')->delete();
        Permission::where('name', 'delete-users-resume')->delete();
    }
}
