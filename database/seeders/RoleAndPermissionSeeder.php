<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Permission Pattern: {action}:{subject}
     *
     * Actions:
     *   - create:  permission to create a new record
     *   - list:    permission to view an index or search for records
     *   - view:    permission to view a specific record
     *   - edit:    permission to edit/update a specific record
     *   - delete:  permission to (soft) delete a specific record
     *   - restore: permission to restore a soft deleted record
     *   - destroy: permission to permanently (hard) delete a record
     *
     * Subjects: model names in kebab-case (e.g., users, jobs, organizations)
     *
     * Wildcards:
     *   - *:* allows all actions on all subjects (superadmin)
     *   - view:* allows viewing any subject
     *   - *:users allows all actions on users
     *
     * Role Types:
     *   - System: Internal Built Ox employees (can access all organizations)
     *   - Organization: External users tied to a specific organization (vendors, customers)
     *
     * RULES:
     *   1. Always grant permissions to a role (not to a user directly)
     *   2. Always check for the most specific permission possible
     *   3. NEVER authorize by checking if a user has a specific role
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define CRUD actions
        $actions = collect([
            'create',
            'list',
            'view',
            'edit',
            'delete',
            'restore',
            'destroy',
        ]);

        // Define subjects (models) - these map to your Built Ox domain
        $subjects = collect([
            'users',
            'user-groups',
            'roles',
            'organizations',   // vendors, subcontractors
            'customers',
            'jobs',
            'contracts',
            'locations',
            'addresses',
            'notification-intents',
            'notification-preferences',
            'dashboard',
            'reports',
        ]);

        // Create special permissions
        Permission::create(['name' => 'access:system']);  // cross-organization access
        Permission::create(['name' => '*:*']);            // superadmin - all permissions

        // Create wildcard action permissions
        foreach ($actions as $action) {
            Permission::create(['name' => "{$action}:*"]);
        }

        // Create subject-specific permissions
        foreach ($subjects as $subject) {
            Permission::create(['name' => "*:{$subject}"]);

            foreach ($actions as $action) {
                Permission::create(['name' => "{$action}:{$subject}"]);
            }
        }

        // Clear the permission cache again after creating all permissions
        // This ensures roles can find them when syncing
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =================================================================
        // SYSTEM ROLES (Built Ox internal employees)
        // =================================================================

        // System Admin - Full access to everything
        $role = Role::create([
            'name' => 'system:admin',
            'type' => RoleType::System,
            'display_name' => 'System Admin',
        ]);
        $role->syncPermissions(['*:*']);

        // System Support - Can view/edit across organizations, limited destructive actions
        $role = Role::create([
            'name' => 'system:support',
            'type' => RoleType::System,
            'display_name' => 'System Support',
        ]);
        $role->syncPermissions([
            'access:system',
            'list:*',
            'view:*',
            'create:*',
            'edit:*',
            // No delete/restore/destroy - support can't delete things
        ]);

        // System Readonly - Can only view data across organizations
        $role = Role::create([
            'name' => 'system:readonly',
            'type' => RoleType::System,
            'display_name' => 'System Read-Only',
        ]);
        $role->syncPermissions([
            'access:system',
            'list:*',
            'view:*',
        ]);

        // =================================================================
        // ORGANIZATION ROLES (Vendors, Subcontractors, their employees)
        // =================================================================

        // Organization Admin - Full access within their organization
        $role = Role::create([
            'name' => 'org:admin',
            'type' => RoleType::Organization,
            'display_name' => 'Organization Admin',
        ]);
        $role->syncPermissions($actions->map(fn ($action) => "{$action}:*")->toArray());

        // Organization Member - Standard access for vendor/subcontractor employees
        $role = Role::create([
            'name' => 'org:member',
            'type' => RoleType::Organization,
            'display_name' => 'Organization Member',
        ]);
        $role->syncPermissions([
            // Jobs - full access (their main workflow)
            '*:jobs',
            // Customers - can view and create (for referrals)
            'list:customers',
            'view:customers',
            'create:customers',
            // Contracts - view only
            'list:contracts',
            'view:contracts',
            // Locations - view only
            'list:locations',
            'view:locations',
            // Dashboard/Reports - view their own org's data
            'view:dashboard',
            'view:reports',
        ]);

        // Organization Viewer - Read-only access (for stakeholders)
        $role = Role::create([
            'name' => 'org:viewer',
            'type' => RoleType::Organization,
            'display_name' => 'Organization Viewer',
        ]);
        $role->syncPermissions([
            'list:jobs',
            'view:jobs',
            'list:customers',
            'view:customers',
            'view:dashboard',
            'view:reports',
        ]);

        // =================================================================
        // CUSTOMER ROLE (for Customer Portal access)
        // =================================================================

        $role = Role::create([
            'name' => 'customer',
            'type' => RoleType::Organization,
            'display_name' => 'Customer',
        ]);
        $role->syncPermissions([
            // Customers can view their own jobs and contracts
            'list:jobs',
            'view:jobs',
            'list:contracts',
            'view:contracts',
        ]);
    }
}
