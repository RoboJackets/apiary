<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddWaiverPaymentMethodPermission extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $p_create_payments_waiver = Permission::firstOrCreate(['name' => 'create-payments-waiver']);

        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($p_create_payments_waiver);
        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_officer->givePermissionTo($p_create_payments_waiver);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'create-payments-waiver')->delete();
    }
}
