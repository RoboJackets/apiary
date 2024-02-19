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

        $approve_travel = Permission::firstOrCreate(['name' => 'approve-travel']);

        $officer = Role::firstOrCreate(['name' => 'officer']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        $officer->givePermissionTo($approve_travel);
        $admin->givePermissionTo($approve_travel);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'approve-travel')->delete();
    }
};
