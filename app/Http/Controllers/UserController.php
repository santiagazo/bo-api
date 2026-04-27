<?php

namespace App\Http\Controllers;

use App\Enums\MembershipScope;
use App\Enums\MembershipStatus;
use App\Http\Resources\UserResource;
use App\Models\Membership;
use App\Models\Role;
use App\Models\User;
use App\Rules\ValidPhoneNumber;
use App\Services\PhoneService;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $companyId = app(CompanyContext::class)->companyId();

        $query = User::query()
            ->with([
                'memberships.company.address',
                'memberships.roles.permissions',
            ])
            ->whereHas('memberships', function ($membershipQuery) use ($companyId) {
                $membershipQuery->where('scope_type', MembershipScope::Company)
                    ->where('status', MembershipStatus::Active)
                    ->where('company_id', $companyId);
            });

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($userQuery) use ($search) {
                $userQuery->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $roleName = $request->string('role');
            $query->whereHas('memberships.roles', function ($roleQuery) use ($roleName) {
                $roleQuery->where('name', $roleName);
            });
        }

        $users = $query->orderBy('name')->paginate(50);

        return UserResource::collection($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $companyId = app(CompanyContext::class)->companyId();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'string', 'max:5'],
            'password' => ['required', 'string', 'min:6'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $roles = $this->resolveAssignableRoles($validated['roles'] ?? [], $companyId);

        $user = DB::transaction(function () use ($validated, $companyId, $roles) {
            $formattedPhone = ! empty($validated['phone'])
                ? $this->phoneService->formatToE164($validated['phone'], $validated['phone_country_code'] ?? 'US')
                : null;

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $formattedPhone,
                'phone_country_code' => $validated['phone_country_code'] ?? 'US',
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            $membership = Membership::create([
                'user_id' => $user->id,
                'scope_type' => MembershipScope::Company,
                'status' => MembershipStatus::Active,
                'company_id' => $companyId,
                'is_primary' => true,
            ]);

            if ($roles->isNotEmpty()) {
                $membership->syncRoles($roles->pluck('name')->all());
            }

            return $user;
        });

        return response()->json(
            new UserResource($user->load(['memberships.company.address', 'memberships.roles.permissions'])),
            201
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json(
            new UserResource($user->load(['memberships.company.address', 'memberships.roles.permissions']))
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $companyId = app(CompanyContext::class)->companyId();
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'string', 'max:5'],
            'password' => ['sometimes', 'string', 'min:6'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $membership = $user->memberships()
            ->where('scope_type', MembershipScope::Company)
            ->where('status', MembershipStatus::Active)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $roles = array_key_exists('roles', $validated)
            ? $this->resolveAssignableRoles($validated['roles'], $companyId)
            : collect();

        DB::transaction(function () use ($user, $validated, $membership, $roles) {
            $updates = [];

            foreach (['name', 'email', 'phone_country_code'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updates[$field] = $validated[$field];
                }
            }

            if (array_key_exists('phone', $validated)) {
                $updates['phone'] = $validated['phone']
                    ? $this->phoneService->formatToE164(
                        $validated['phone'],
                        $validated['phone_country_code'] ?? $user->phone_country_code ?? 'US'
                    )
                    : null;
            }

            if (array_key_exists('password', $validated)) {
                $updates['password'] = Hash::make($validated['password']);
            }

            if ($updates !== []) {
                $user->update($updates);
            }

            if ($roles->isNotEmpty() || array_key_exists('roles', $validated)) {
                $membership->syncRoles($roles->pluck('name')->all());
            }
        });

        return response()->json(
            new UserResource($user->fresh()->load(['memberships.company.address', 'memberships.roles.permissions']))
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $company = app(CompanyContext::class)->company();

        abort_unless($company, 404);

        DB::transaction(function () use ($user, $company) {
            $membership = $user->memberships()
                ->where('scope_type', MembershipScope::Company)
                ->where('status', MembershipStatus::Active)
                ->where('company_id', $company->id)
                ->firstOrFail();

            $wasPrimary = $membership->is_primary;

            $membership->delete();

            if ($wasPrimary) {
                $nextPrimaryMembership = $user->memberships()
                    ->where('scope_type', MembershipScope::Company)
                    ->where('status', MembershipStatus::Active)
                    ->whereNull('deleted_at')
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->first();

                if ($nextPrimaryMembership) {
                    $nextPrimaryMembership->update(['is_primary' => true]);
                }
            }

            if (! $user->memberships()->whereNull('deleted_at')->exists()) {
                $user->delete();
            }
        });

        return response()->json(['message' => 'User removed from the active company successfully.']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'string', 'max:5'],
        ]);

        $updateData = [];

        foreach (['name', 'email', 'phone_country_code'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        if (array_key_exists('phone', $validated)) {
            $updateData['phone'] = $validated['phone']
                ? $this->phoneService->formatToE164(
                    $validated['phone'],
                    $validated['phone_country_code'] ?? $user->phone_country_code ?? 'US'
                )
                : null;
        }

        if ($updateData !== []) {
            $user->update($updateData);
        }

        return response()->json(
            new UserResource($user->fresh()->load(['memberships.company.address', 'memberships.roles.permissions']))
        );
    }

    protected function resolveAssignableRoles(array $roleNames, int $companyId)
    {
        $uniqueNames = array_values(array_unique($roleNames));

        if ($uniqueNames === []) {
            return collect();
        }

        $roles = Role::query()
            ->whereIn('name', $uniqueNames)
            ->where('scope_type', 'company')
            ->where(function ($roleQuery) use ($companyId) {
                $roleQuery->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            })
            ->get();

        if ($roles->count() !== count($uniqueNames)) {
            throw ValidationException::withMessages([
                'roles' => ['One or more selected roles do not belong to the active company scope.'],
            ]);
        }

        return $roles;
    }
}
