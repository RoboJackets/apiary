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

        $create_dues_transactions = Permission::where('name', '=', 'create-dues-transactions')->first();
        $update_dues_transactions = Permission::where('name', '=', 'update-dues-transactions')->first();

        if ($create_dues_transactions !== null && $officer !== null) {
            $officer->revokePermissionTo($create_dues_transactions);
        }

        if ($update_dues_transactions !== null && $officer !== null) {
            $officer->revokePermissionTo($update_dues_transactions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $create_dues_transactions = Permission::firstOrCreate(['name' => 'create-dues-transactions']);

        $update_dues_transactions = Permission::firstOrCreate(['name' => 'update-dues-transactions']);

        Role::firstOrCreate(['name' => 'officer'])->givePermissionTo($create_dues_transactions);
        Role::firstOrCreate(['name' => 'officer'])->givePermissionTo($update_dues_transactions);
    }
};
