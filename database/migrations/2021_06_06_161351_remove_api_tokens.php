<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'read-users-api_token')->delete();

        if (Schema::hasColumn('users', 'api_token')) {
            Schema::table('users', static function (Blueprint $table): void {
                $table->dropColumn('api_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $read_users_api_token = Permission::firstOrCreate(['name' => 'read-users-api_token']);
        $r_admin = Role::firstOrCreate(['name' => 'admin']);
        $r_admin->givePermissionTo($read_users_api_token);

        Schema::table('users', static function (Blueprint $table): void {
            $table->string('api_token', 32);
        });
    }
};
