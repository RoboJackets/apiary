<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHiddenTeamsPermission extends Migration
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

        Permission::firstOrCreate(['name' => 'read-hidden-teams']);
        Permission::firstOrCreate(['name' => 'update-hidden-teams']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo('read-hidden-teams');
        $role->givePermissionTo('update-hidden-teams');
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

        Permission::where('name', 'read-hidden-teams')->delete();
        Permission::where('name', 'update-hidden-teams')->delete();
    }
}
