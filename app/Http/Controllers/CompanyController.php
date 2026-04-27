<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Address;
use App\Models\Company;
use App\Services\PhoneService;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends Controller
{
    public function __construct(
        protected PhoneService $phoneService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Company::class);

        $activeCompanyId = app(CompanyContext::class)->companyId();
        $query = Company::query()
            ->with('address')
            ->withCount(['users', 'locations', 'jobs', 'organizations']);

        if ($activeCompanyId) {
            $query->whereKey($activeCompanyId);
        }

        if (request()->filled('search')) {
            $search = request()->string('search');
            $query->where(function ($companyQuery) use ($search) {
                $companyQuery->where('name', 'ilike', "%{$search}%")
                    ->orWhere('legal_name', 'ilike', "%{$search}%")
                    ->orWhere('primary_email', 'ilike', "%{$search}%");
            });
        }

        $companies = $query->orderBy('name')->paginate(50);

        return CompanyResource::collection($companies);
    }

    public function current(): CompanyResource
    {
        $company = app(CompanyContext::class)->company();

        abort_unless($company, 404);

        $this->authorize('view', $company);

        $company->load('address');
        $company->loadCount(['users', 'locations', 'jobs', 'organizations']);

        return new CompanyResource($company);
    }

    public function store(StoreCompanyRequest $request): CompanyResource
    {
        $validated = $request->validated();

        if (isset($validated['primary_phone'])) {
            $formattedPhone = $this->phoneService->formatToE164(
                $validated['primary_phone'],
                $validated['phone_country_code'] ?? 'US'
            );

            $validated['primary_phone'] = $formattedPhone;
        }

        if (isset($validated['address'])) {
            $address = Address::create($validated['address']);
            $validated['address_id'] = $address->id;
            unset($validated['address']);
        }

        $company = Company::create($validated);
        $company->load('address');
        $company->loadCount(['users', 'locations', 'jobs', 'organizations']);

        return new CompanyResource($company);
    }

    public function show(Company $company): CompanyResource
    {
        $this->authorize('view', $company);

        $company->load('address');
        $company->loadCount(['users', 'locations', 'jobs', 'organizations']);

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $this->authorize('update', $company);

        $validated = $request->validated();

        if (isset($validated['primary_phone'])) {
            $validated['primary_phone'] = $this->phoneService->formatToE164(
                $validated['primary_phone'],
                $validated['phone_country_code'] ?? $company->phone_country_code ?? 'US'
            );
        }

        if (isset($validated['address'])) {
            if ($company->address_id && $company->address) {
                $company->address->update($validated['address']);
            } else {
                $address = Address::create($validated['address']);
                $validated['address_id'] = $address->id;
            }

            unset($validated['address']);
        }

        $company->update($validated);
        $company->load('address');
        $company->loadCount(['users', 'locations', 'jobs', 'organizations']);

        return new CompanyResource($company);
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully.']);
    }

    public function restore(int $id): CompanyResource
    {
        $company = Company::withTrashed()->findOrFail($id);

        $this->authorize('restore', $company);

        $company->restore();
        $company->load('address');
        $company->loadCount(['users', 'locations', 'jobs', 'organizations']);

        return new CompanyResource($company);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $company = Company::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $company);

        $company->forceDelete();

        return response()->json(['message' => 'Company permanently deleted.']);
    }
}
