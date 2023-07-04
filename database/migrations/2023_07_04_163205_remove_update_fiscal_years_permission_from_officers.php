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

        $update_fiscal_years = Permission::where('name', '=', 'update-fiscal-years')->first();

        if ($update_fiscal_years !== null && $officer !== null) {
            $officer->revokePermissionTo($update_fiscal_years);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $update_fiscal_years = Permission::firstOrCreate(['name' => 'update-fiscal-years']);

        Role::firstOrCreate(['name' => 'officer'])->givePermissionTo($update_fiscal_years);
    }
};
