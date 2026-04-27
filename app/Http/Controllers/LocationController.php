<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Address;
use App\Models\Location;
use App\Services\PhoneService;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $companyId = app(CompanyContext::class)->companyId();

        $query = Location::query()
            ->with('address')
            ->withCount('jobs')
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($locationQuery) use ($search) {
                $locationQuery->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $locations = $query->orderBy('name')->paginate(50);

        return LocationResource::collection($locations);
    }

    public function store(StoreLocationRequest $request): LocationResource
    {
        $companyId = app(CompanyContext::class)->companyId();
        $validated = $request->validated();

        $location = DB::transaction(function () use ($validated, $companyId) {
            if (isset($validated['phone'])) {
                $validated['phone'] = $this->phoneService->formatToE164(
                    $validated['phone'],
                    $validated['phone_country_code'] ?? 'US'
                );
            }

            if (isset($validated['address'])) {
                $address = Address::create($validated['address']);
                $validated['address_id'] = $address->id;
                unset($validated['address']);
            }

            return Location::create([
                ...$validated,
                'company_id' => $companyId,
            ]);
        });

        return new LocationResource(
            $location->load(['company', 'address'])->loadCount('jobs')
        );
    }

    public function show(Location $location): LocationResource
    {
        $this->authorize('view', $location);

        return new LocationResource(
            $location->load(['company', 'address'])->loadCount('jobs')
        );
    }

    public function update(UpdateLocationRequest $request, Location $location): LocationResource
    {
        $validated = $request->validated();

        DB::transaction(function () use ($location, $validated) {
            if (isset($validated['phone'])) {
                $validated['phone'] = $this->phoneService->formatToE164(
                    $validated['phone'],
                    $validated['phone_country_code'] ?? $location->phone_country_code ?? 'US'
                );
            }

            if (isset($validated['address'])) {
                if ($location->address_id && $location->address) {
                    $location->address->update($validated['address']);
                } else {
                    $address = Address::create($validated['address']);
                    $validated['address_id'] = $address->id;
                }

                unset($validated['address']);
            }

            if ($validated !== []) {
                $location->update($validated);
            }
        });

        return new LocationResource(
            $location->fresh()->load(['company', 'address'])->loadCount('jobs')
        );
    }

    public function destroy(Location $location): JsonResponse
    {
        $this->authorize('delete', $location);

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully.']);
    }
}
