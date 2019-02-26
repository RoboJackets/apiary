<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class ProjectManagerPermissions extends Migration
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

        $permissionNova = Permission::firstOrCreate(['name' => 'access-nova']);

        $permissionswag = Permission::firstOrCreate(['name' => 'distribute-swag']);

        $permissiondt = Permission::firstOrCreate(['name' => 'read-dues-transactions']);

        $permissionpayments = Permission::firstOrCreate(['name' => 'read-payments']);

        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_pm = Role::firstOrCreate(['name' => 'project-manager']);
        $r_tl = Role::firstOrCreate(['name' => 'team-lead']);
        $r_trainer = Role::firstOrCreate(['name' => 'trainer']);
        $r_admin = Role::firstOrCreate(['name' => 'admin']);

        $r_officer->givePermissionTo($permissionNova);
        $r_pm->givePermissionTo($permissionNova);
        $r_tl->givePermissionTo($permissionNova);
        $r_trainer->givePermissionTo($permissionNova);
        $r_admin->givePermissionTo($permissionNova);

        $r_officer->givePermissionTo($permissionswag);
        $r_pm->givePermissionTo($permissionswag);
        $r_tl->givePermissionTo($permissionswag);
        $r_admin->givePermissionTo($permissionswag);

        $r_officer->givePermissionTo($permissiondt);
        $r_pm->givePermissionTo($permissiondt);
        $r_tl->givePermissionTo($permissiondt);
        $r_admin->givePermissionTo($permissiondt);

        $r_officer->givePermissionTo($permissionpayments);
        $r_pm->givePermissionTo($permissionpayments);
        $r_tl->givePermissionTo($permissionpayments);
        $r_admin->givePermissionTo($permissionpayments);
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
        Permission::where('name', 'distribute-swag')->delete();
    }
}
