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

        $view_docusign_envelopes = Permission::firstOrCreate(['name' => 'view-docusign-envelopes']);

        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo($view_docusign_envelopes);

        Role::firstOrCreate(['name' => 'officer'])->givePermissionTo($view_docusign_envelopes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');
        Permission::where('name', 'view-docusign-envelopes')->delete();
    }
};
