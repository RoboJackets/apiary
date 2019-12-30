<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

class AddTrainerRole extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $role = Role::firstOrCreate(['name' => 'trainer']);
        $role->givePermissionTo('create-attendance');
        $role->givePermissionTo('read-teams-hidden');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = Role::where('name', 'trainer')->first();

        if (null === $role) {
            return;
        }

        $role->delete();
    }
}
