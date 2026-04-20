<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserGroupResource;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserGroupController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', UserGroup::class);

        $authUser = $request->user();
        $query = UserGroup::query()
            ->with('organization')
            ->withCount('users');

        if (! $authUser->hasSystemAccess()) {
            $query->where('organization_id', $authUser->organization_id);
        } elseif ($request->filled('organization_id')) {
            $query->where('organization_id', $request->integer('organization_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $groups = $query->orderBy('name')->paginate(50);

        return UserGroupResource::collection($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', UserGroup::class);

        [$validated, $organizationId] = $this->validatePayload($request);

        $group = DB::transaction(function () use ($validated, $organizationId) {
            $group = UserGroup::create([
                'organization_id' => $organizationId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (array_key_exists('user_ids', $validated)) {
                $group->users()->sync($validated['user_ids']);
            }

            return $group;
        });

        return response()->json(
            new UserGroupResource($group->load(['organization', 'users'])->loadCount('users')),
            201
        );
    }

    public function show(UserGroup $userGroup): JsonResponse
    {
        $this->authorize('view', $userGroup);

        return response()->json(
            new UserGroupResource($userGroup->load(['organization', 'users'])->loadCount('users'))
        );
    }

    public function update(Request $request, UserGroup $userGroup): JsonResponse
    {
        $this->authorize('update', $userGroup);

        [$validated, $organizationId] = $this->validatePayload($request, $userGroup);

        DB::transaction(function () use ($userGroup, $validated, $organizationId) {
            $userGroup->update([
                'organization_id' => $organizationId,
                'name' => $validated['name'] ?? $userGroup->name,
                'description' => array_key_exists('description', $validated)
                    ? $validated['description']
                    : $userGroup->description,
                'is_active' => $validated['is_active'] ?? $userGroup->is_active,
            ]);

            if (array_key_exists('user_ids', $validated)) {
                $userGroup->users()->sync($validated['user_ids']);
            }
        });

        return response()->json(
            new UserGroupResource($userGroup->fresh()->load(['organization', 'users'])->loadCount('users'))
        );
    }

    public function destroy(UserGroup $userGroup): JsonResponse
    {
        $this->authorize('delete', $userGroup);

        $userGroup->delete();

        return response()->json(['message' => 'User group deleted successfully.']);
    }

    /**
     * @return array{0: array<string, mixed>, 1: int}
     */
    protected function validatePayload(Request $request, ?UserGroup $userGroup = null): array
    {
        $authUser = $request->user();
        $organizationId = $authUser->hasSystemAccess()
            ? (int) ($request->input('organization_id') ?: $userGroup?->organization_id ?: $authUser->organization_id)
            : (int) $authUser->organization_id;

        $request->merge(['organization_id' => $organizationId]);

        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => [
                $userGroup ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('user_groups', 'name')
                    ->ignore($userGroup?->id)
                    ->where(fn ($query) => $query
                        ->where('organization_id', $organizationId)
                        ->whereNull('deleted_at')),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $this->assertUsersBelongToOrganization($validated['user_ids'] ?? [], $organizationId);

        return [$validated, $organizationId];
    }

    /**
     * @param  array<int>  $userIds
     */
    protected function assertUsersBelongToOrganization(array $userIds, int $organizationId): void
    {
        $uniqueIds = array_values(array_unique($userIds));

        if ($uniqueIds === []) {
            return;
        }

        $matchingUsers = User::query()
            ->whereIn('id', $uniqueIds)
            ->where('organization_id', $organizationId)
            ->count();

        if ($matchingUsers !== count($uniqueIds)) {
            throw ValidationException::withMessages([
                'user_ids' => ['All selected users must belong to the same organization as the user group.'],
            ]);
        }
    }
}
