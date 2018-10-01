<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class AddDuesTransactionsOwnPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Permission::firstOrCreate(['name' => 'create-dues-transactions-own']);

        $role = Role::findByName('admin');
        $role->givePermissionTo('create-dues-transactions-own');

        $roleNames = ['officer-ii', 'officer-i', 'core', 'member', 'non-member'];

        foreach ($roleNames as $roleName) {
            $role = Role::findByName($roleName);
            $role->revokePermissionTo('create-dues-transactions');
            $role->givePermissionTo('create-dues-transactions-own');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $adminRole = Role::findByName('admin');
        $adminRole->revokePermissionTo('create-dues-transactions-own');

        $roleNames = ['officer-i', 'officer-ii', 'core', 'member', 'non-member'];
        foreach ($roleNames as $roleName) {
            $role = Role::findByName($roleName);
            $role->givePermissionTo('create-dues-transactions');
            $role->revokePermissionTo('create-dues-transactions-own');
        }
        Permission::findByName('create-dues-transactions-own')->delete();
    }
}
