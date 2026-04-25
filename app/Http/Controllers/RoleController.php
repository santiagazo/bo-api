<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Support\TenantContext;
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

        $tenantOrganizationId = app(TenantContext::class)->organizationId();
        $query->where(function ($query) use ($request, $tenantOrganizationId) {
            $query->whereNull('organization_id');

            if ($request->user()->hasGlobalAccess() && $request->boolean('include_all')) {
                return;
            }

            $query->orWhere('organization_id', $request->integer('organization_id') ?: $tenantOrganizationId);
        });

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

        $organizationId = $this->resolveRoleOrganizationId($request, $validated);

        $role = DB::transaction(function () use ($validated, $organizationId) {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'type' => $validated['type'],
                'organization_id' => $organizationId,
                'guard_name' => 'web',
            ]);

            if (! empty($validated['permissions'])) {
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

        $organizationId = array_key_exists('organization_id', $validated) || array_key_exists('type', $validated)
            ? $this->resolveRoleOrganizationId($request, $validated)
            : $role->organization_id;

        DB::transaction(function () use ($role, $validated, $organizationId) {
            $updates = [];

            foreach (['name', 'display_name', 'type'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updates[$field] = $validated[$field];
                }
            }

            if (array_key_exists('organization_id', $validated) || array_key_exists('type', $validated)) {
                $updates['organization_id'] = $organizationId;
            }

            $role->update($updates);

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

        // Prevent deletion of built-in global roles
        if (in_array($role->name, ['global:admin', 'global:support', 'global:readonly', 'system:admin', 'system:support', 'system:readonly'])) {
            return response()->json([
                'message' => 'Cannot delete built-in global roles.',
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

    protected function resolveRoleOrganizationId(Request $request, array $validated): ?int
    {
        $type = $validated['type'] ?? null;

        if ($type instanceof RoleType && $type->isGlobalScope()) {
            abort_unless($request->user()->hasGlobalAccess(), 403);

            return null;
        }

        if (is_string($type) && in_array($type, [RoleType::Global->value, RoleType::System->value], true)) {
            abort_unless($request->user()->hasGlobalAccess(), 403);

            return null;
        }

        if ($request->user()->hasGlobalAccess() && array_key_exists('organization_id', $validated)) {
            return $validated['organization_id'];
        }

        return app(TenantContext::class)->organizationId();
    }
}
