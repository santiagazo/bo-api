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
     *   - Global: Internal Built Ox employees (can access all organizations)
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
        Permission::firstOrCreate(['name' => 'access:global']);  // cross-organization access
        Permission::firstOrCreate(['name' => '*:*']);            // superadmin - all permissions

        // Create wildcard action permissions
        foreach ($actions as $action) {
            Permission::firstOrCreate(['name' => "{$action}:*"]);
        }

        // Create subject-specific permissions
        foreach ($subjects as $subject) {
            Permission::firstOrCreate(['name' => "*:{$subject}"]);

            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}:{$subject}"]);
            }
        }

        $permissionsForActions = fn (array $allowedActions): array => $subjects
            ->flatMap(fn ($subject) => collect($allowedActions)->map(
                fn ($action) => "{$action}:{$subject}"
            ))
            ->values()
            ->all();

        // Clear the permission cache again after creating all permissions
        // This ensures roles can find them when syncing
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =================================================================
        // GLOBAL ROLES (Built Ox internal employees)
        // =================================================================

        // Global Admin - Full access to everything
        $role = Role::updateOrCreate([
            'name' => 'global:admin',
        ], [
            'type' => RoleType::Global,
            'display_name' => 'Global Admin',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['*:*']);

        // Global Support - Can help across organizations, but without destructive actions
        $role = Role::updateOrCreate([
            'name' => 'global:support',
        ], [
            'type' => RoleType::Global,
            'display_name' => 'Global Support',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions([
            'access:global',
            ...$permissionsForActions(['create', 'list', 'view', 'edit']),
        ]);

        // Global Readonly - Can only view data across organizations
        $role = Role::updateOrCreate([
            'name' => 'global:readonly',
        ], [
            'type' => RoleType::Global,
            'display_name' => 'Global Read-Only',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions([
            'access:global',
            ...$permissionsForActions(['list', 'view']),
        ]);

        // =================================================================
        // ORGANIZATION ROLES (Vendors, Subcontractors, their employees)
        // =================================================================

        // Organization Admin - Full access within their organization
        $role = Role::updateOrCreate([
            'name' => 'org:admin',
        ], [
            'type' => RoleType::Organization,
            'display_name' => 'Organization Admin',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissionsForActions($actions->all()));

        // Organization Support - Can create and edit within their organization,
        // but cannot perform destructive actions.
        $role = Role::updateOrCreate([
            'name' => 'org:support',
        ], [
            'type' => RoleType::Organization,
            'display_name' => 'Organization Support',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissionsForActions(['create', 'list', 'view', 'edit']));

        // Organization Readonly - Can only view data in their own organization.
        $role = Role::updateOrCreate([
            'name' => 'org:readonly',
        ], [
            'type' => RoleType::Organization,
            'display_name' => 'Organization Read-Only',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissionsForActions(['list', 'view']));

        // =================================================================
        // CUSTOMER ROLE (for Customer Portal access)
        // =================================================================

        $role = Role::updateOrCreate([
            'name' => 'customer',
        ], [
            'type' => RoleType::Organization,
            'display_name' => 'Customer',
            'guard_name' => 'web',
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
