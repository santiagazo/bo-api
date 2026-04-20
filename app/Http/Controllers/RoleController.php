<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query()
            ->withCount('users')
            ->with('permissions');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by organization (for org-specific roles)
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('display_name', 'ilike', "%{$request->search}%");
            });
        }

        $roles = $query->orderBy('type')->orderBy('name')->paginate(50);

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(RoleType::class)],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'type' => $validated['type'],
                'organization_id' => $validated['organization_id'] ?? null,
                'guard_name' => 'web',
            ]);

            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            return $role;
        });

        return response()->json(
            new RoleResource($role->load('permissions')),
            201
        );
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return response()->json(
            new RoleResource($role->load('permissions')->loadCount('users'))
        );
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::enum(RoleType::class)],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        DB::transaction(function () use ($role, $validated) {
            $role->update(array_filter([
                'name' => $validated['name'] ?? null,
                'display_name' => $validated['display_name'] ?? null,
                'type' => $validated['type'] ?? null,
                'organization_id' => array_key_exists('organization_id', $validated) 
                    ? $validated['organization_id'] 
                    : $role->organization_id,
            ], fn ($value) => !is_null($value)));

            if (array_key_exists('permissions', $validated)) {
                $role->syncPermissions($validated['permissions']);
            }
        });

        return response()->json(
            new RoleResource($role->fresh()->load('permissions'))
        );
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        // Prevent deletion of system roles
        if (in_array($role->name, ['system:admin', 'system:support', 'system:readonly'])) {
            return response()->json([
                'message' => 'Cannot delete system roles.',
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role with assigned users. Please reassign users first.',
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}



