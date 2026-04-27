<?php

namespace App\Http\Controllers;

use App\Enums\RoleScope;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $companyId = app(CompanyContext::class)->companyId();

        $query = Role::query()
            ->with('permissions')
            ->withCount('memberships')
            ->where('scope_type', RoleScope::Company);

        if ($companyId) {
            $query->where(function ($roleQuery) use ($companyId) {
                $roleQuery->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            });
        } else {
            $query->whereNull('company_id');
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($roleQuery) use ($search) {
                $roleQuery->where('name', 'ilike', "%{$search}%")
                    ->orWhere('display_name', 'ilike', "%{$search}%");
            });
        }

        $roles = $query->orderBy('display_name')->paginate(50);

        return RoleResource::collection($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $companyId = app(CompanyContext::class)->companyId();
        $assignablePermissions = $this->assignablePermissionNames($request);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9:-]+$/',
                Rule::unique('roles', 'name')->where(function ($query) use ($companyId) {
                    $query->where('guard_name', 'web')
                        ->where(function ($scopedQuery) use ($companyId) {
                            $scopedQuery->whereNull('company_id')
                                ->orWhere('company_id', $companyId);
                        });
                }),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name', Rule::in($assignablePermissions)],
        ]);

        $role = DB::transaction(function () use ($validated, $companyId) {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'scope_type' => RoleScope::Company,
                'company_id' => $companyId,
                'guard_name' => 'web',
            ]);

            if (! empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            return $role;
        });

        return response()->json(new RoleResource($role->load('permissions')), 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return response()->json(
            new RoleResource($role->load('permissions')->loadCount('memberships'))
        );
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $companyId = app(CompanyContext::class)->companyId();
        $assignablePermissions = $this->assignablePermissionNames($request);
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9:-]+$/',
                Rule::unique('roles', 'name')
                    ->ignore($role->id)
                    ->where(function ($query) use ($companyId) {
                        $query->where('guard_name', 'web')
                            ->where(function ($scopedQuery) use ($companyId) {
                                $scopedQuery->whereNull('company_id')
                                    ->orWhere('company_id', $companyId);
                            });
                    }),
            ],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name', Rule::in($assignablePermissions)],
        ]);

        DB::transaction(function () use ($validated, $role) {
            $updates = collect($validated)
                ->only(['name', 'display_name'])
                ->all();

            if ($updates !== []) {
                $role->update($updates);
            }

            if (array_key_exists('permissions', $validated)) {
                $role->syncPermissions($validated['permissions']);
            }
        });

        return response()->json(
            new RoleResource($role->fresh()->load('permissions')->loadCount('memberships'))
        );
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        if ($role->memberships()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete a role that is currently assigned.',
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    protected function assignablePermissionNames(Request $request): array
    {
        $company = app(CompanyContext::class)->company();

        if (! $company) {
            return Permission::query()->pluck('name')->all();
        }

        return $request->user()
            ?->permissionsForUiContext($company)
            ->values()
            ->all() ?? [];
    }
}
