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

        $create_events = Permission::firstOrCreate(['name' => 'create-events']);
        $read_events = Permission::firstOrCreate(['name' => 'read-events']);
        $update_events = Permission::firstOrCreate(['name' => 'update-events']);
        $update_events_own = Permission::firstOrCreate(['name' => 'update-events-own']);

        $project_manager = Role::firstOrCreate(['name' => 'project-manager']);
        $team_lead = Role::firstOrCreate(['name' => 'team-lead']);

        $project_manager->givePermissionTo($create_events);
        $project_manager->givePermissionTo($read_events);
        $project_manager->givePermissionTo($update_events);
        $project_manager->givePermissionTo($update_events_own);

        $team_lead->givePermissionTo($create_events);
        $team_lead->givePermissionTo($read_events);
        $team_lead->givePermissionTo($update_events);
        $team_lead->givePermissionTo($update_events_own);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $project_manager = Role::where('name', '=', 'project-manager')->first();
        $team_lead = Role::where('name', '=', 'team-lead')->first();

        $create_events = Permission::where('name', '=', 'create-events')->first();
        $read_events = Permission::where('name', '=', 'read-events')->first();
        $update_events = Permission::where('name', '=', 'update-events')->first();
        $update_events_own = Permission::where('name', '=', 'update-events-own')->first();
        $delete_events = Permission::where('name', '=', 'delete-events')->first();

        if ($project_manager !== null) {
            if ($create_events !== null) {
                $project_manager->revokePermissionTo($create_events);
            }

            if ($read_events !== null) {
                $project_manager->revokePermissionTo($read_events);
            }

            if ($update_events !== null) {
                $project_manager->revokePermissionTo($update_events);
            }

            if ($update_events_own !== null) {
                $project_manager->revokePermissionTo($update_events_own);
            }

            if ($delete_events !== null) {
                $project_manager->revokePermissionTo($delete_events);
            }
        }

        if ($team_lead !== null) {
            if ($create_events !== null) {
                $team_lead->revokePermissionTo($create_events);
            }

            if ($read_events !== null) {
                $team_lead->revokePermissionTo($read_events);
            }

            if ($update_events !== null) {
                $team_lead->revokePermissionTo($update_events);
            }

            if ($update_events_own !== null) {
                $team_lead->revokePermissionTo($update_events_own);
            }

            if ($delete_events !== null) {
                $team_lead->revokePermissionTo($delete_events);
            }
        }
    }
};
