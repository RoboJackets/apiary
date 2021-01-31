<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

class GiveNonMembersAccessToReadMerchandise extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $memberRole = Role::firstOrCreate(['name' => 'non-member']);
        $memberRole->givePermissionTo('read-merchandise');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // nope
    }
}
