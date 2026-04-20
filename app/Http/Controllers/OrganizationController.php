<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Address;
use App\Models\Organization;
use App\Services\PhoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {
    }

    /**
     * Display a listing of organizations.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Organization::class);

        $query = Organization::query()->withCount(['users', 'locations', 'jobs']);

        // Filter by type
        if (request()->has('type')) {
            $query->where('type', request()->type);
        }

        // Search by name or email
        if (request()->has('search')) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . request()->search . '%')
                  ->orWhere('email', 'ilike', '%' . request()->search . '%');
            });
        }

        $organizations = $query->orderBy('name')->paginate(50);

        return OrganizationResource::collection($organizations);
    }

    /**
     * Store a newly created organization.
     */
    public function store(StoreOrganizationRequest $request): OrganizationResource
    {
        $validated = $request->validated();

        // Handle phone number formatting
        if (isset($validated['phone'])) {
            $phoneCountryCode = $validated['phone_country_code'] ?? 'US';
            $formattedPhone = $this->phoneService->formatToE164($validated['phone'], $phoneCountryCode);
            if ($formattedPhone) {
                $validated['phone'] = $formattedPhone;
            } else {
                unset($validated['phone']);
            }
        }

        // Handle address creation if provided
        if (isset($validated['address'])) {
            $address = Address::create($validated['address']);
            $validated['address_id'] = $address->id;
            unset($validated['address']);
        }

        $organization = Organization::create($validated);

        $organization->loadCount(['users', 'locations', 'jobs']);

        return new OrganizationResource($organization);
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization): OrganizationResource
    {
        $this->authorize('view', $organization);

        $organization->load(['address']);
        $organization->loadCount(['users', 'locations', 'jobs']);

        return new OrganizationResource($organization);
    }

    /**
     * Update the specified organization.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): OrganizationResource
    {
        $validated = $request->validated();

        // Handle phone number formatting
        if (isset($validated['phone'])) {
            $phoneCountryCode = $validated['phone_country_code'] ?? $organization->phone_country_code ?? 'US';
            $formattedPhone = $this->phoneService->formatToE164($validated['phone'], $phoneCountryCode);
            if ($formattedPhone) {
                $validated['phone'] = $formattedPhone;
            } else {
                unset($validated['phone']);
            }
        }

        // Handle address creation/update if provided
        if (isset($validated['address'])) {
            if ($organization->address_id) {
                // Update existing address
                $organization->address->update($validated['address']);
            } else {
                // Create new address
                $address = Address::create($validated['address']);
                $validated['address_id'] = $address->id;
            }
            unset($validated['address']);
        }

        $organization->update($validated);

        $organization->load(['address']);
        $organization->loadCount(['users', 'locations', 'jobs']);

        return new OrganizationResource($organization);
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully']);
    }

    /**
     * Restore the specified organization.
     */
    public function restore(int $id): OrganizationResource
    {
        $organization = Organization::withTrashed()->findOrFail($id);

        $this->authorize('restore', $organization);

        $organization->restore();

        $organization->load(['address']);
        $organization->loadCount(['users', 'locations', 'jobs']);

        return new OrganizationResource($organization);
    }

    /**
     * Permanently delete the specified organization.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $organization = Organization::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $organization);

        $organization->forceDelete();

        return response()->json(['message' => 'Organization permanently deleted']);
    }
}

