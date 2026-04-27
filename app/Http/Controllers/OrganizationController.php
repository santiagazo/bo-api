<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Address;
use App\Models\Organization;
use App\Services\PhoneService;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Organization::class);

        $companyId = app(CompanyContext::class)->companyId();

        $query = Organization::query()
            ->with(['address', 'typeAssignments'])
            ->withCount('contacts')
            ->where('company_id', $companyId);

        if ($request->filled('type')) {
            $query->whereHas('typeAssignments', function ($typeQuery) use ($request) {
                $typeQuery->where('type', $request->string('type'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($organizationQuery) use ($search) {
                $organizationQuery->where('display_name', 'ilike', "%{$search}%")
                    ->orWhere('legal_name', 'ilike', "%{$search}%")
                    ->orWhere('primary_email', 'ilike', "%{$search}%");
            });
        }

            $organizations = $query->orderBy('display_name')->paginate(50);

        return OrganizationResource::collection($organizations);
    }

    public function store(StoreOrganizationRequest $request): OrganizationResource
    {
        $companyId = app(CompanyContext::class)->companyId();
        $validated = $request->validated();

        $organization = DB::transaction(function () use ($validated, $companyId) {
            if (isset($validated['primary_phone'])) {
                $validated['primary_phone'] = $this->phoneService->formatToE164(
                    $validated['primary_phone'],
                    $validated['phone_country_code'] ?? 'US'
                );
            }

            if (isset($validated['address'])) {
                $address = Address::create($validated['address']);
                $validated['address_id'] = $address->id;
                unset($validated['address']);
            }

            $types = $validated['types'] ?? [];
            unset($validated['types']);

            $organization = Organization::create([
                ...$validated,
                'company_id' => $companyId,
            ]);

            $this->syncTypes($organization, $types);

            return $organization;
        });

        return new OrganizationResource(
            $organization->load(['company', 'address', 'typeAssignments'])->loadCount('contacts')
        );
    }

    public function show(Organization $organization): OrganizationResource
    {
        $this->authorize('view', $organization);

        return new OrganizationResource(
            $organization->load(['company', 'address', 'typeAssignments'])->loadCount('contacts')
        );
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): OrganizationResource
    {
        $validated = $request->validated();

        DB::transaction(function () use ($organization, $validated) {
            if (isset($validated['primary_phone'])) {
                $validated['primary_phone'] = $this->phoneService->formatToE164(
                    $validated['primary_phone'],
                    $validated['phone_country_code'] ?? $organization->phone_country_code ?? 'US'
                );
            }

            if (isset($validated['address'])) {
                if ($organization->address_id && $organization->address) {
                    $organization->address->update($validated['address']);
                } else {
                    $address = Address::create($validated['address']);
                    $validated['address_id'] = $address->id;
                }

                unset($validated['address']);
            }

            $types = $validated['types'] ?? null;
            unset($validated['types']);

            if ($validated !== []) {
                $organization->update($validated);
            }

            if ($types !== null) {
                $this->syncTypes($organization, $types);
            }
        });

        return new OrganizationResource(
            $organization->fresh()->load(['company', 'address', 'typeAssignments'])->loadCount('contacts')
        );
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully.']);
    }

    protected function syncTypes(Organization $organization, array $types): void
    {
        $organization->typeAssignments()->delete();

        foreach ($types as $index => $type) {
            $organization->typeAssignments()->create([
                'type' => $type['type'],
                'is_primary' => $type['is_primary'] ?? $index === 0,
            ]);
        }
    }
}
