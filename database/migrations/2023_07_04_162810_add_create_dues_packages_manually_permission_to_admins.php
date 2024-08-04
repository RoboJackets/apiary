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

        $create_dues_packages_manually = Permission::firstOrCreate(['name' => 'create-dues-packages-manually']);

        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo($create_dues_packages_manually);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'create-dues-packages-manually')->delete();
    }
};
