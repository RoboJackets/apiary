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

        $team_lead = Role::where('name', '=', 'team-lead')->first();

        $read_payments = Permission::where('name', '=', 'read-payments')->first();

        if ($read_payments !== null && $team_lead !== null) {
            $team_lead->revokePermissionTo($read_payments);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $read_payments = Permission::firstOrCreate(['name' => 'read-payments']);

        Role::firstOrCreate(['name' => 'team-lead'])->givePermissionTo($read_payments);
    }
};
