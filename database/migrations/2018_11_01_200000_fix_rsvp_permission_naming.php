<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class FixRsvpPermissionNaming extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $create_rsvp = Permission::findByName('create-rsvp');
        $create_rsvp->name = 'create-rsvps';
        $create_rsvp->save();

        $create_rsvp_own = Permission::findByName('create-rsvp-own');
        $create_rsvp_own->name = 'create-rsvps-own';
        $create_rsvp_own->save();

        $delete_rsvp_own = Permission::findByName('delete-rsvp-own');
        $delete_rsvp_own->name = 'delete-rsvps-own';
        $delete_rsvp_own->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $create_rsvps = Permission::findByName('create-rsvps');
        $create_rsvps->name = 'create-rsvp';
        $create_rsvps->save();

        $create_rsvps_own = Permission::findByName('create-rsvps-own');
        $create_rsvps_own->name = 'create-rsvp-own';
        $create_rsvps_own->save();

        $delete_rsvps_own = Permission::findByName('delete-rsvps-own');
        $delete_rsvps_own->name = 'delete-rsvp-own';
        $delete_rsvps_own->save();
    }
}
