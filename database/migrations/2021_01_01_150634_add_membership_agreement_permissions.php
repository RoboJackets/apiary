<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddMembershipAgreementPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $createTemplate = Permission::firstOrCreate(['name' => 'create-membership-agreement-templates']);
        $updateTemplate = Permission::firstOrCreate(['name' => 'update-membership-agreement-templates']);
        $createSignature = Permission::firstOrCreate(['name' => 'upload-signatures']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($createTemplate);
        $admin->givePermissionTo($updateTemplate);
        $admin->givePermissionTo($createSignature);

        $officer = Role::firstOrCreate(['name' => 'officer']);
        $officer->givePermissionTo($createTemplate);
        $officer->givePermissionTo($updateTemplate);
        $officer->givePermissionTo($createSignature);

        $projectManager = Role::firstOrCreate(['name' => 'project-manager']);
        $projectManager->givePermissionTo($createSignature);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // I don't want to write this
    }
}
