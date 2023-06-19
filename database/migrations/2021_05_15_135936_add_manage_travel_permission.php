<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $manage_travel = Permission::firstOrCreate(['name' => 'manage-travel']);

        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($manage_travel);
        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_officer->givePermissionTo($manage_travel);
        $r_pm = Role::firstOrCreate(['name' => 'project-manager']);
        $r_pm->givePermissionTo($manage_travel);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'manage-travel')->delete();
    }
};
