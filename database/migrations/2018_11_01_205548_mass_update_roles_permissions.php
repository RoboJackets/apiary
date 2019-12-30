<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MassUpdateRolesPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
        $p_rc_c = Permission::firstOrCreate(['name' => 'create-recruiting-campaigns']);
        $p_rc_r = Permission::firstOrCreate(['name' => 'read-recruiting-campaigns']);
        $p_rc_u = Permission::firstOrCreate(['name' => 'update-recruiting-campaigns']);
        $p_rc_d = Permission::firstOrCreate(['name' => 'delete-recruiting-campaigns']);

        // And finally let's assign permissions to the new roles
        $r_member_perms = ['read-users-own', 'update-users-own', 'read-events', 'update-events-own',
            'create-rsvps-own', 'read-rsvps-own', 'update-rsvps-own', 'delete-rsvps-own',
            'create-payments-own', 'read-payments-own', 'read-dues-packages',
            'create-dues-transactions-own', 'read-dues-transactions-own', 'read-recruiting-visits-own',
            'update-recruiting-visits-own', 'delete-recruiting-visits-own', 'read-attendance-own',
            'read-teams', 'read-teams-membership-own', 'update-teams-membership-own',
        ];
        $r_member->syncPermissions($r_member_perms);
        $r_non_member->syncPermissions($r_member_perms);

        $r_trainer_perms = ['read-users', 'read-users-emergency_contact', 'create-attendance', 'read-attendance',
            'read-teams-hidden',
        ];
        $r_trainer->syncPermissions($r_trainer_perms);

        $r_tl_perms = ['read-users', 'read-users-emergency_contact', 'read-teams-membership'];
        $r_tl->syncPermissions($r_tl_perms);

        $r_pm_perms = ['read-users', 'read-users-emergency_contact', 'read-rsvps', 'create-payments',
            'create-payments-cash', 'create-payments-check', 'create-recruiting-visits',
            'create-attendance', 'read-attendance', 'read-teams-membership', 'update-teams',
        ];
        $r_pm->syncPermissions($r_pm_perms);

        $r_officer_perms = ['read-users', 'read-users-gtid', 'read-users-emergency_contact', 'create-events',
            'update-events', 'create-rsvps', 'read-rsvps', 'delete-rsvps', 'create-payments',
            'create-payments-cash', 'create-payments-check', 'read-payments', 'create-dues-packages',
            'update-dues-packages', 'create-dues-transactions', 'read-dues-transactions',
            'update-dues-transactions', 'create-recruiting-visits', 'read-recruiting-visits',
            'create-recruiting-campaigns', 'read-recruiting-campaigns', 'update-recruiting-campaigns',
            'send-notifications', 'create-attendance', 'read-attendance', 'update-attendance',
            'create-teams', 'read-teams-membership', 'update-teams', 'read-teams-hidden',
        ];
        $r_officer->syncPermissions($r_officer_perms);

        $r_admin_perms = Permission::all();
        $r_admin->syncPermissions($r_admin_perms);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Delete the roles that we aren't using any more
        $r_officer = Role::where('name', 'officer')->delete();
        $r_pm = Role::where('name', 'project-manager')->delete();
        $r_tl = Role::where('name', 'team-lead')->delete();

        // Re-create the old roles that we deleted earlier
        $r_officer_ii = Role::firstOrCreate(['name' => 'officer-ii']);
        $r_officer_i = Role::firstOrCreate(['name' => 'officer-i']);
        $r_core = Role::firstOrCreate(['name' => 'core']);

        // Fetch existing roles for mass update of permissions
        $r_member = Role::firstOrCreate(['name' => 'member']);
        $r_non_member = Role::firstOrCreate(['name' => 'non-member']);
        $r_admin = Role::firstOrCreate(['name' => 'admin']);

        // Delete newly-created permissions
        $p_u_gtid = Permission::where('name', 'read-users-gtid')->delete();
        $p_u_api = Permission::where('name', 'read-users-api_token')->delete();
        $p_u_ec = Permission::where('name', 'read-users-emergency_contact')->delete();
        $p_u_d = Permission::where('name', 'read-users-demographics')->delete();
        $p_p_cash = Permission::where('name', 'create-payments-cash')->delete();
        $p_p_check = Permission::where('name', 'create-payments-check')->delete();
        $p_p_square = Permission::where('name', 'create-payments-square')->delete();
        $p_p_sqc = Permission::where('name', 'create-payments-squarecash')->delete();
        $p_p_swipe = Permission::where('name', 'create-payments-swipe')->delete();
        $p_d_own = Permission::where('name', 'create-dues-transactions-own')->delete();
        $p_t_mem = Permission::where('name', 'read-teams-membership')->delete();
        $p_t_mem_own = Permission::where('name', 'read-teams-membership-own')->delete();

        // Reassign permissions (yes this is gross)
        $r_admin_perms = Permission::all();
        $r_admin->syncPermissions($r_admin_perms);

        $r_officer_ii_perms = ['read-users', 'read-users-own', 'update-users', 'update-users-own', 'create-events',
            'read-events', 'update-events', 'update-events-own', 'delete-events', 'create-rsvps-own', 'read-rsvps',
            'read-rsvps-own', 'update-rsvps', 'update-rsvps-own', 'delete-rsvps', 'delete-rsvps-own', 'create-payments',
            'read-payments', 'read-payments-own', 'create-dues-packages', 'read-dues-packages', 'update-dues-packages',
            'read-dues-transactions', 'read-dues-transactions-own', 'update-dues-transactions',
            'create-recruiting-visits', 'read-recruiting-visits', 'read-recruiting-visits-own',
            'update-recruiting-visits', 'update-recruiting-visits-own', 'create-attendance', 'read-attendance',
            'read-attendance-own', 'update-attendance', 'delete-attendance', 'create-teams', 'read-teams',
            'update-teams', 'update-teams-membership-own',
        ];
        $r_officer_ii->syncPermissions($r_officer_ii_perms);

        $r_officer_i_perms = ['read-users', 'read-users-own', 'update-users', 'update-users-own', 'create-events',
            'read-events', 'update-events-own', 'create-rsvps-own', 'read-rsvps', 'read-rsvps-own', 'update-rsvps-own',
            'delete-rsvps-own', 'create-payments', 'read-payments', 'read-payments-own', 'read-dues-packages',
            'read-dues-transactions', 'read-dues-transactions-own', 'update-dues-transactions',
            'create-recruiting-visits', 'read-recruiting-visits-own', 'update-recruiting-visits-own',
            'create-attendance', 'read-attendance', 'read-attendance-own', 'update-attendance', 'delete-attendance',
            'create-teams', 'read-teams', 'update-teams', 'update-teams-membership-own',
        ];
        $r_officer_i->syncPermissions($r_officer_i_perms);

        $r_member_perms = ['read-users-own', 'update-users-own', 'read-events', 'create-rsvps-own', 'read-rsvps-own',
            'update-rsvps-own', 'delete-rsvps-own', 'read-payments-own', 'read-dues-packages',
            'read-dues-transactions-own', 'read-recruiting-visits-own', 'update-recruiting-visits-own',
            'read-attendance-own', 'read-teams', 'update-teams-membership-own',
        ];
        $r_member->syncPermissions($r_member_perms);

        $r_nonmember_perms = ['read-users-own', 'update-users-own', 'read-events', 'create-rsvps-own', 'read-rsvps-own',
            'update-rsvps-own', 'delete-rsvps-own', 'read-payments-own', 'read-dues-packages',
            'read-dues-transactions-own', 'read-recruiting-visits-own', 'update-recruiting-visits-own',
            'read-attendance-own', 'read-teams', 'update-teams-membership-own',
        ];
        $r_non_member->syncPermissions($r_nonmember_perms);

        $r_core_perms = ['read-users-own', 'update-users-own', 'create-events', 'read-events', 'update-events-own',
            'create-rsvps-own', 'read-rsvps-own', 'update-rsvps-own', 'delete-rsvps-own', 'read-payments-own',
            'read-dues-packages', 'read-dues-transactions-own', 'create-recruiting-visits',
            'read-recruiting-visits-own', 'update-recruiting-visits-own', 'create-attendance',
            'read-attendance', 'read-attendance-own', 'create-teams', 'read-teams', 'update-teams-membership-own',
        ];
        $r_core->syncPermissions($r_core_perms);
    }
}
