<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        if (!DB::getSchemaBuilder()->hasTable($permissionsTable)) {
            return;
        }

        // Fresh databases seed permissions later; this migration is for already-seeded environments.
        if (DB::table($permissionsTable)->count() === 0) {
            return;
        }

        $now = now();
        $permissionNames = [
            '*:user-groups',
            'create:user-groups',
            'list:user-groups',
            'view:user-groups',
            'edit:user-groups',
            'delete:user-groups',
            'restore:user-groups',
            'destroy:user-groups',
        ];

        foreach ($permissionNames as $name) {
            DB::table($permissionsTable)->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        if (!DB::getSchemaBuilder()->hasTable($permissionsTable)) {
            return;
        }

        DB::table($permissionsTable)
            ->where('guard_name', 'web')
            ->whereIn('name', [
                '*:user-groups',
                'create:user-groups',
                'list:user-groups',
                'view:user-groups',
                'edit:user-groups',
                'delete:user-groups',
                'restore:user-groups',
                'destroy:user-groups',
            ])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
