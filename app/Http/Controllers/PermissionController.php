<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     * Permissions are generally read-only (seeded from RoleAndPermissionSeeder).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Permission::class);

        $query = Permission::query();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'ilike', "%{$request->search}%");
        }

        // Group permissions (useful for UI)
        $permissions = $query->orderBy('name')->get();

        return PermissionResource::collection($permissions);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        return response()->json(
            new PermissionResource($permission)
        );
    }

    /**
     * Get permissions grouped by subject for UI.
     */
    public function grouped(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = Permission::orderBy('name')->get();

        // Group by subject (the part after the colon)
        $grouped = $permissions->groupBy(function ($permission) {
            $parts = explode(':', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        })->map(function ($permissions, $subject) {
            return [
                'subject' => $subject,
                'permissions' => PermissionResource::collection($permissions),
            ];
        })->values();

        return response()->json($grouped);
    }
}



