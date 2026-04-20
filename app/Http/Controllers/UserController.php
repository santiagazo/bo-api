<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationType;
use App\Enums\RoleType;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Rules\ValidPhoneNumber;
use App\Services\PhoneService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected PhoneService $phoneService;

    public function __construct(PhoneService $phoneService)
    {
        $this->phoneService = $phoneService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()
            ->with(['organization', 'roles.permissions']);

        $tenantOrganizationId = app(TenantContext::class)->organizationId();
        if ($tenantOrganizationId && ! $request->user()->hasSystemAccess()) {
            $query->where('organization_id', $tenantOrganizationId);
        } elseif ($tenantOrganizationId && ! $request->boolean('include_all')) {
            $query->where('organization_id', $tenantOrganizationId);
        } elseif ($request->has('organization_id')) {
            $query->where('organization_id', $request->integer('organization_id'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                    ->orWhere('email', 'ilike', "%{$request->search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('name')->paginate(50);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', new ValidPhoneNumber($request->input('phone_country_code', 'US'))],
            'phone_country_code' => ['sometimes', 'string', 'max:5'],
            'password' => ['required', 'string', 'min:6'],
            'organization_id' => ['sometimes', 'exists:organizations,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $organizationId = $this->resolveTargetOrganizationId($request, $validated['organization_id'] ?? null);
        $this->assertAssignableRoles($request, $validated['roles'] ?? [], $organizationId);

        $user = DB::transaction(function () use ($validated, $organizationId) {
            $formattedPhone = $this->phoneService->formatToE164(
                $validated['phone'],
                $validated['phone_country_code'] ?? 'US'
            );

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $formattedPhone,
                'phone_country_code' => $validated['phone_country_code'] ?? 'US',
                'password' => Hash::make($validated['password']),
                'organization_id' => $organizationId,
            ]);

            if (! empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            return $user;
        });

        return response()->json(
            new UserResource($user->load(['organization', 'roles.permissions'])),
            201
        );
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json(
            new UserResource($user->load(['organization', 'roles.permissions']))
        );
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'string', new ValidPhoneNumber($request->input('phone_country_code', 'US'))],
            'phone_country_code' => ['sometimes', 'string', 'max:5'],
            'password' => ['sometimes', 'string', 'min:6'],
            'organization_id' => ['sometimes', 'exists:organizations,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $organizationId = array_key_exists('organization_id', $validated)
            ? $this->resolveTargetOrganizationId($request, $validated['organization_id'])
            : null;
        $targetOrganizationId = $organizationId ?? $user->organization_id;

        if (array_key_exists('roles', $validated)) {
            $this->assertAssignableRoles($request, $validated['roles'], (int) $targetOrganizationId);
        }

        DB::transaction(function () use ($user, $validated, $organizationId) {
            $updateData = [];

            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['phone'])) {
                $updateData['phone'] = $this->phoneService->formatToE164(
                    $validated['phone'],
                    $validated['phone_country_code'] ?? $user->phone_country_code ?? 'US'
                );
            }
            if (isset($validated['phone_country_code'])) {
                $updateData['phone_country_code'] = $validated['phone_country_code'];
            }
            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }
            if ($organizationId !== null) {
                $updateData['organization_id'] = $organizationId;
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            if (array_key_exists('roles', $validated)) {
                $user->syncRoles($validated['roles']);
            }
        });

        return response()->json(
            new UserResource($user->fresh()->load(['organization', 'roles.permissions']))
        );
    }

    protected function resolveTargetOrganizationId(Request $request, ?int $requestedOrganizationId): int
    {
        $tenantOrganizationId = app(TenantContext::class)->organizationId();

        if (! $request->user()->hasSystemAccess()) {
            return (int) $tenantOrganizationId;
        }

        return (int) ($requestedOrganizationId ?: $tenantOrganizationId);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    protected function assertAssignableRoles(Request $request, array $roleNames, int $organizationId): void
    {
        if ($roleNames === []) {
            return;
        }

        $roles = Role::query()
            ->whereIn('name', array_values(array_unique($roleNames)))
            ->get();

        if ($roles->count() !== count(array_unique($roleNames))) {
            throw ValidationException::withMessages([
                'roles' => ['One or more selected roles do not exist.'],
            ]);
        }

        $targetIsGlobal = Organization::query()
            ->whereKey($organizationId)
            ->where('type', OrganizationType::Global)
            ->exists();
        $actorHasSystemAccess = $request->user()->hasSystemAccess();

        foreach ($roles as $role) {
            if ($role->type === RoleType::System) {
                if (! $actorHasSystemAccess || ! $targetIsGlobal) {
                    throw ValidationException::withMessages([
                        'roles' => ['System roles can only be assigned to users in the Built Ox global organization.'],
                    ]);
                }

                continue;
            }

            if ($role->organization_id !== null && (int) $role->organization_id !== $organizationId) {
                throw ValidationException::withMessages([
                    'roles' => ['Organization roles must belong to the same organization as the user.'],
                ]);
            }
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'string', new ValidPhoneNumber($request->input('phone_country_code', 'US'))],
            'phone_country_code' => ['sometimes', 'string', 'max:5'],
        ]);

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        if (isset($validated['phone'])) {
            $updateData['phone'] = $this->phoneService->formatToE164(
                $validated['phone'],
                $validated['phone_country_code'] ?? $user->phone_country_code ?? 'US'
            );
        }
        if (isset($validated['phone_country_code'])) {
            $updateData['phone_country_code'] = $validated['phone_country_code'];
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json(
            new UserResource($user->fresh()->load(['organization', 'roles.permissions']))
        );
    }
}
