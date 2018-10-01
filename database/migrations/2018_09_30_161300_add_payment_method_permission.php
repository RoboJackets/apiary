<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class AddPaymentMethodPermission extends Migration
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

        Permission::firstOrCreate(['name' => 'create-payments-cash']);
        Permission::firstOrCreate(['name' => 'create-payments-check']);
        Permission::firstOrCreate(['name' => 'create-payments-swipe']);
        Permission::firstOrCreate(['name' => 'create-payments-square']);
        Permission::firstOrCreate(['name' => 'create-payments-squarecash']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo('create-payments-cash');
        $role->givePermissionTo('create-payments-check');

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo('create-payments-cash');
        $role->givePermissionTo('create-payments-check');

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo('create-payments-cash');
        $role->givePermissionTo('create-payments-check');
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

        Permission::where('name', 'create-payments-cash')->delete();
        Permission::where('name', 'create-payments-check')->delete();
        Permission::where('name', 'create-payments-swipe')->delete();
        Permission::where('name', 'create-payments-square')->delete();
        Permission::where('name', 'create-payments-squarecash')->delete();
    }
}
