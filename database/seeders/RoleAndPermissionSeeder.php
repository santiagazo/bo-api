<?php

namespace Database\Seeders;

use App\Enums\RoleScope;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = collect([
            'create',
            'list',
            'view',
            'edit',
            'delete',
            'restore',
            'destroy',
        ]);

        $subjects = collect([
            'companies',
            'organizations',
            'users',
            'roles',
            'customers',
            'jobs',
            'locations',
            'addresses',
            'dashboard',
            'reports',
        ]);

        Permission::firstOrCreate(['name' => 'access:platform', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'impersonate:companies', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => '*:*', 'guard_name' => 'web']);

        foreach ($actions as $action) {
            Permission::firstOrCreate(['name' => "{$action}:*", 'guard_name' => 'web']);
        }

        foreach ($subjects as $subject) {
            Permission::firstOrCreate(['name' => "*:{$subject}", 'guard_name' => 'web']);

            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}:{$subject}", 'guard_name' => 'web']);
            }
        }

        $permissionsForActions = fn (array $allowedActions): array => $subjects
            ->flatMap(fn ($subject) => collect($allowedActions)->map(
                fn ($action) => "{$action}:{$subject}"
            ))
            ->values()
            ->all();

        $permissionsForActionsAndSubjects = fn (array $allowedActions, array $allowedSubjects): array => collect($allowedSubjects)
            ->flatMap(fn ($subject) => collect($allowedActions)->map(
                fn ($action) => "{$action}:{$subject}"
            ))
            ->values()
            ->all();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $platformAdmin = Role::updateOrCreate(
            ['name' => 'platform:admin', 'guard_name' => 'web'],
            ['scope_type' => RoleScope::Platform, 'display_name' => 'Platform Admin']
        );
        $platformAdmin->syncPermissions(['*:*']);

        $platformSupport = Role::updateOrCreate(
            ['name' => 'platform:support', 'guard_name' => 'web'],
            ['scope_type' => RoleScope::Platform, 'display_name' => 'Platform Support']
        );
        $platformSupport->syncPermissions([
            'access:platform',
            'impersonate:companies',
            ...$permissionsForActions(['create', 'list', 'view', 'edit']),
        ]);

        $platformReadonly = Role::updateOrCreate(
            ['name' => 'platform:readonly', 'guard_name' => 'web'],
            ['scope_type' => RoleScope::Platform, 'display_name' => 'Platform Read-Only']
        );
        $platformReadonly->syncPermissions([
            'access:platform',
            ...$permissionsForActions(['list', 'view']),
        ]);

        $companyAdmin = Role::updateOrCreate(
            ['name' => 'company:admin', 'guard_name' => 'web'],
            ['scope_type' => RoleScope::Company, 'display_name' => 'Company Admin']
        );
        $companyAdmin->syncPermissions([
            'view:companies',
            'edit:companies',
            ...$permissionsForActionsAndSubjects($actions->all(), [
                'organizations',
                'users',
                'roles',
                'customers',
                'jobs',
                'locations',
                'addresses',
                'dashboard',
                'reports',
            ]),
        ]);

        $companySupport = Role::updateOrCreate(
            ['name' => 'company:support', 'guard_name' => 'web'],
            ['scope_type' => RoleScope::Company, 'display_name' => 'Company Support']
        );
        $companySupport->syncPermissions([
            'view:companies',
            ...$permissionsForActionsAndSubjects(['create', 'list', 'view', 'edit'], [
                'organizations',
                'users',
                'roles',
                'customers',
                'jobs',
                'locations',
                'addresses',
                'dashboard',
                'reports',
            ]),
        ]);

        $companyReadonly = Role::updateOrCreate(
            ['name' => 'company:readonly', 'guard_name' => 'web'],
            ['scope_type' => RoleScope::Company, 'display_name' => 'Company Read-Only']
        );
        $companyReadonly->syncPermissions([
            'view:companies',
            ...$permissionsForActionsAndSubjects(['list', 'view'], [
                'organizations',
                'users',
                'roles',
                'customers',
                'jobs',
                'locations',
                'addresses',
                'dashboard',
                'reports',
            ]),
        ]);
    }
}
