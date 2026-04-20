<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Rules\ValidPhoneNumber;
use App\Services\PhoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

        // Filter by organization
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
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
            'organization_id' => ['required', 'exists:organizations,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user = DB::transaction(function () use ($validated) {
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
                'organization_id' => $validated['organization_id'],
            ]);

            if (!empty($validated['roles'])) {
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

        DB::transaction(function () use ($user, $validated) {
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
            if (isset($validated['organization_id'])) {
                $updateData['organization_id'] = $validated['organization_id'];
            }

            if (!empty($updateData)) {
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

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json(
            new UserResource($user->fresh()->load(['organization', 'roles.permissions']))
        );
    }
}



