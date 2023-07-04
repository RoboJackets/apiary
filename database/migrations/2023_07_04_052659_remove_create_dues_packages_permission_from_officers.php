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

        $officer = Role::where('name', '=', 'officer')->first();

        $create_dues_packages = Permission::where('name', '=', 'create-dues-packages')->first();

        if ($create_dues_packages !== null && $officer !== null) {
            $officer->revokePermissionTo($create_dues_packages);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $create_dues_packages = Permission::firstOrCreate(['name' => 'create-dues-packages']);

        Role::firstOrCreate(['name' => 'officer'])->givePermissionTo($create_dues_packages);
    }
};
