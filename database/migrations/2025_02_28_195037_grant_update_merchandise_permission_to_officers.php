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

        $update_merchandise = Permission::firstOrCreate(['name' => 'update-merchandise']);

        $officer = Role::firstOrCreate(['name' => 'officer']);

        $officer->givePermissionTo($update_merchandise);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $officer = Role::where('name', '=', 'officer')->first();

        $update_merchandise = Permission::where('name', '=', 'update-merchandise')->first();

        if ($update_merchandise !== null && $officer !== null) {
            $officer->revokePermissionTo($update_merchandise);
        }
    }
};
