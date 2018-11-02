<?php

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MassUpdateRolesPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // First, delete the roles that we aren't using any more
        $officer_ii = Role::where('name', 'officer-ii')->delete();
        $officer_i = Role::where('name', 'officer-i')->delete();
        $core = Role::where('name', 'core')->delete();

        // Next, let's create the new roles
        $r_officer = Role::firstOrCreate(['name' => 'officer']);
        $r_pm = Role::firstOrCreate(['name' => 'project-manager']);
        $r_tl = Role::firstOrCreate(['name' => 'team-lead']);
        $r_trainer = Role::firstOrCreate(['name' => 'trainer']);

        // Fetch existing roles for mass update of permissions
        $r_member = Role::firstOrCreate(['name' => 'member']);
        $r_non_member = Role::firstOrCreate(['name' => 'non-member']);
        $r_admin = Role::firstOrCreate(['name' => 'admin']);

        // Create new permissions that didn't exist before
        $p_u_gtid = Permission::firstOrCreate(['name' => 'read-users-gtid']);
        $p_u_api = Permission::firstOrCreate(['name' => 'read-users-api_token']);
        $p_u_ec = Permission::firstOrCreate(['name' => 'read-users-emergency_contact']);
        $p_u_d = Permission::firstOrCreate(['name' => 'read-users-demographics']);
        $p_p_cash = Permission::firstOrCreate(['name' => 'create-payments-cash']);
        $p_p_check = Permission::firstOrCreate(['name' => 'create-payments-check']);
        $p_p_square = Permission::firstOrCreate(['name' => 'create-payments-square']);
        $p_p_sqc = Permission::firstOrCreate(['name' => 'create-payments-squarecash']);
        $p_p_swipe = Permission::firstOrCreate(['name' => 'create-payments-swipe']);
        $p_d_own = Permission::firstOrCreate(['name' => 'create-dues-transactions-own']);
        $p_t_mem = Permission::firstOrCreate(['name' => 'read-teams-membership']);
        $p_t_mem_own = Permission::firstOrCreate(['name' => 'read-teams-membership-own']);

        // And finally let's assign permissions to the new roles
        $r_member_perms = ['read-users-own', 'update-users-own', 'read-events', 'update-events-own',
                            'create-rsvps-own', 'read-rsvps-own', 'update-rsvps-own', 'delete-rsvps-own',
                            'create-payments-own', 'read-payments-own', 'read-dues-packages',
                            'create-dues-transactions-own', 'read-dues-transactions-own', 'read-recruiting-visits-own',
                            'update-recruiting-visits-own', 'delete-recruiting-visits-own', 'read-attendance-own',
                            'read-teams', 'read-teams-membership-own', 'update-teams-membership-own'];
        $r_member->syncPermissions($r_member_perms);
        $r_non_member->syncPermissions($r_member_perms);

        $r_trainer_perms = ['read-users', 'read-users-emergency_contact', 'create-attendance', 'read-attendance',
                            'read-teams-hidden'];
        $r_trainer->syncPermissions($r_trainer_perms);

        $r_tl_perms = ['read-users', 'read-users-emergency_contact', 'read-teams-membership'];
        $r_tl->syncPermissions($r_tl_perms);

        $r_pm_perms = ['read-users', 'read-users-emergency_contact', 'read-rsvps', 'create-payments',
                        'create-payments-cash', 'create-payments-check', 'create-recruiting-visits',
                        'create-attendance', 'read-attendance', 'read-teams-membership', 'update-teams'];
        $r_pm->syncPermissions($r_pm_perms);

        $r_officer_perms = ['read-users', 'read-users-gtid', 'read-users-emergency_contact', 'create-events',
                            'update-events', 'create-rsvps', 'read-rsvps', 'delete-rsvps', 'create-payments',
                            'create-payments-cash', 'create-payments-check', 'read-payments', 'create-dues-packages',
                            'update-dues-packages', 'create-dues-transactions', 'read-dues-transactions',
                            'update-dues-transactions', 'create-recruiting-visits', 'read-recruiting-visits',
                            'create-recruiting-campaigns', 'read-recruiting-campaigns', 'update-recruiting-campaigns',
                            'send-notifications', 'create-attendance', 'read-attendance', 'update-attendance',
                            'create-teams', 'read-teams-membership', 'update-teams', 'read-teams-hidden'];
        $r_officer->syncPermissions($r_officer_perms);

        $r_admin_perms = Permission::all();
        $r_admin->syncPermissions($r_admin_perms);
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
    }
}
