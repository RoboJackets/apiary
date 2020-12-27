<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddFiscalYearPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $create = Permission::firstOrCreate(['name' => 'create-fiscal-years']);
        $update = Permission::firstOrCreate(['name' => 'update-fiscal-years']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($create);
        $admin->givePermissionTo($update);

        $officer = Role::firstOrCreate(['name' => 'officer']);
        $officer->givePermissionTo($create);
        $officer->givePermissionTo($update);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // I don't want to write this
    }
}
