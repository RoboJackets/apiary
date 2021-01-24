<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddMerchPermissions extends Migration
{
    /**
     * Permissions to be used elsewhere.
     *
     * @var array<string>
     */
    public $allPermissions = [
        'create-merchandise',
        'read-merchandise',
        'update-merchandise',
        'delete-merchandise',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create Permissions
        foreach ($this->allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo('create-merchandise');
        $adminRole->givePermissionTo('read-merchandise');
        $adminRole->givePermissionTo('update-merchandise');
        $adminRole->givePermissionTo('delete-merchandise');

        $officerRole = Role::firstOrCreate(['name' => 'officer']);
        $officerRole->givePermissionTo('create-merchandise');
        // Merch should generally be immutable, but admins retain update for edge cases.

        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $memberRole->givePermissionTo('read-merchandise');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        foreach ($this->allPermissions as $permission) {
            $dbPerm = Permission::where('name', $permission)->first();
            if (null === $dbPerm) {
                continue;
            }
            $dbPerm->delete();
        }
    }
}
