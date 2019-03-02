<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteOldOfficerRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::where('name', 'officer-i')->delete();
        Role::where('name', 'officer-ii')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNova = Permission::firstOrCreate(['name' => 'access-nova']);

        $role = Role::firstOrCreate(['name' => 'officer-ii']);
        $role->givePermissionTo($permissionNova);

        $role = Role::firstOrCreate(['name' => 'officer-i']);
        $role->givePermissionTo($permissionNova);
    }
}
