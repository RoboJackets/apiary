<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
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

        $create_attendance = Permission::firstOrCreate(['name' => 'create-attendance']);
        $read_events = Permission::firstOrCreate(['name' => 'read-events']);
        $read_teams = Permission::firstOrCreate(['name' => 'read-teams']);
        $read_teams_hidden = Permission::firstOrCreate(['name' => 'read-teams-hidden']);
        $read_users = Permission::firstOrCreate(['name' => 'read-users']);

        $shared_device = Role::firstOrCreate(['name' => 'shared-device']);

        $shared_device->givePermissionTo($create_attendance);
        $shared_device->givePermissionTo($read_events);
        $shared_device->givePermissionTo($read_teams);
        $shared_device->givePermissionTo($read_teams_hidden);
        $shared_device->givePermissionTo($read_users);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Role::where('name', 'shared-device')->delete();
    }
};
