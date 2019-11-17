<?php

use Illuminate\Database\Migrations\Migration;

class AddWaiverPaymentMethodPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $p_create_payments_waiver = Permission::firstOrCreate(['name' => 'create-payments-waiver']);

        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($create_payments_waiver);
        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_officer->givePermissionTo($p_create_payments_waiver);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'create-payments-waiver')->delete();
    }
}
