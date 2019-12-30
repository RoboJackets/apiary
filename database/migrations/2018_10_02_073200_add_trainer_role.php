<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

class AddTrainerRole extends Migration
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

        $role = Role::firstOrCreate(['name' => 'trainer']);
        $role->givePermissionTo('create-attendance');
        $role->givePermissionTo('read-teams-hidden');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $role = Role::where('name', 'trainer')->first();

        if (null !== $role) {
            $role->delete();
        }
    }
}
