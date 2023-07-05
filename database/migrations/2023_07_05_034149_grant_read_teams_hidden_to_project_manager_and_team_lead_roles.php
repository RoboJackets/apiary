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

        $read_teams_hidden = Permission::firstOrCreate(['name' => 'read-teams-hidden']);

        Role::firstOrCreate(['name' => 'project-manager'])->givePermissionTo($read_teams_hidden);
        Role::firstOrCreate(['name' => 'team-lead'])->givePermissionTo($read_teams_hidden);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $project_manager = Role::where('name', '=', 'project-manager')->first();
        $team_lead = Role::where('name', '=', 'team-lead')->first();

        $read_teams_hidden = Permission::where('name', '=', 'read-teams-hidden')->first();

        if ($read_teams_hidden !== null && $project_manager !== null) {
            $project_manager->revokePermissionTo($read_teams_hidden);
        }

        if ($read_teams_hidden !== null && $team_lead !== null) {
            $team_lead->revokePermissionTo($read_teams_hidden);
        }
    }
};
