<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderLocationsRequest;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $company = app(CompanyContext::class)->company();
        $companyId = $company?->id;
        $user = $request->user();

        $query = Location::query()
            ->with('address')
            ->withCount('jobs')
            ->where('company_id', $companyId);

        if ($company && $user?->hasLocationRestrictions($company)) {
            $query->whereIn('id', $user->accessibleLocationIds($company));
        }

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

        $locations = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(50);

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

            if (blank($validated['color'] ?? null)) {
                $validated['color'] = $this->nextLocationColor($companyId);
            }

            if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
                $validated['sort_order'] = $this->nextSortOrder($companyId);
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

            if (array_key_exists('color', $validated)) {
                if (blank($validated['color'])) {
                    unset($validated['color']);
                }
            }

            if (array_key_exists('sort_order', $validated) && $validated['sort_order'] === null) {
                unset($validated['sort_order']);
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

    public function reorder(ReorderLocationsRequest $request): JsonResponse
    {
        $company = app(CompanyContext::class)->company();
        $user = $request->user();

        abort_unless(
            $company !== null && $user?->hasPermissionForCompany('edit:locations', $company),
            403
        );

        abort_if(
            $company !== null && $user?->hasLocationRestrictions($company),
            403,
            'Location-restricted users cannot reorder company locations.'
        );

        $locationIds = collect($request->validated('location_ids'))
            ->map(fn ($id) => (int) $id)
            ->values();

        $companyLocationIds = Location::query()
            ->where('company_id', $company->id)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        abort_unless(
            $locationIds->sort()->values()->all() === $companyLocationIds->sort()->values()->all(),
            422,
            'The submitted location order must include every location in the company exactly once.'
        );

        $timestamp = Carbon::now();

        Location::query()->upsert(
            $locationIds
                ->values()
                ->map(fn (int $id, int $index) => [
                    'id' => $id,
                    'sort_order' => $index,
                    'updated_at' => $timestamp,
                ])
                ->all(),
            ['id'],
            ['sort_order', 'updated_at']
        );

        return response()->json([
            'message' => 'Location order updated successfully.',
        ]);
    }

    protected function nextSortOrder(int $companyId): int
    {
        return (int) Location::query()
            ->where('company_id', $companyId)
            ->max('sort_order') + 1;
    }

    protected function nextLocationColor(int $companyId): string
    {
        $locationCount = Location::query()
            ->where('company_id', $companyId)
            ->count();

        return Location::DEFAULT_COLORS[$locationCount % count(Location::DEFAULT_COLORS)]->value;
    }
}
