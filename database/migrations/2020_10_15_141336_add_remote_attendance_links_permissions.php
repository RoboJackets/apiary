<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddRemoteAttendanceLinksPermissions extends Migration
{
    /**
     * Permissions to be used elsewhere.
     *
     * @var array<string>
     */
    public $allPermissions = [
        'create-remote-attendance-links',
        'read-remote-attendance-links',
        'update-remote-attendance-links',
        'delete-remote-attendance-links',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create Permissions
        foreach ($this->allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo('create-remote-attendance-links');
        $adminRole->givePermissionTo('read-remote-attendance-links');
        $adminRole->givePermissionTo('update-remote-attendance-links');
        $adminRole->givePermissionTo('delete-remote-attendance-links');

        $officerRole = Role::firstOrCreate(['name' => 'officer']);
        $officerRole->givePermissionTo('create-remote-attendance-links');
        $officerRole->givePermissionTo('read-remote-attendance-links');

        $projectManagerRole = Role::firstOrCreate(['name' => 'project-manager']);
        $projectManagerRole->givePermissionTo('create-remote-attendance-links');
        $projectManagerRole->givePermissionTo('read-remote-attendance-links');

        $teamLeadRole = Role::firstOrCreate(['name' => 'team-lead']);
        $teamLeadRole->givePermissionTo('create-remote-attendance-links');
        $teamLeadRole->givePermissionTo('read-remote-attendance-links');

        $trainerRole = Role::firstOrCreate(['name' => 'trainer']);
        $trainerRole->givePermissionTo('create-remote-attendance-links');
        $trainerRole->givePermissionTo('read-remote-attendance-links');
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

        foreach ($this->allPermissions as $permission) {
            $dbPerm = Permission::where('name', $permission)->first();
            if (null === $dbPerm) {
                continue;
            }
            $dbPerm->delete();
        }
    }
}
