<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RemoveApiTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'read-users-api_token')->delete();

        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app()['cache']->forget('spatie.permission.cache');

        $read_users_api_token = Permission::firstOrCreate(['name' => 'read-users-api_token']);
        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($read_users_api_token);

        Schema::table('users', static function (Blueprint $table) {
            $table->string('api_token', 32);
        });
    }
}
