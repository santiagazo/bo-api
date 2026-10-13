<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\LocationResource;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Organization;
use App\Support\CompanyContext;
use App\Support\LocationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $company = app(CompanyContext::class)->company();
        $location = app(LocationContext::class)->location();
        $user = $request->user();

        abort_unless($company, 404);
        abort_unless($user, 401);
        abort_unless(
            $user->hasPermissionForCompany('view:dashboard', $company),
            403,
            'You do not have permission to view this dashboard.'
        );

        $accessibleLocationIds = $user->accessibleLocationIds($company, true);
        $isLocationScoped = $location !== null;

        $jobsQuery = Job::query()->where('company_id', $company->id);

        if ($location) {
            $jobsQuery->where('location_id', $location->id);
        } elseif ($user->hasLocationRestrictions($company)) {
            $jobsQuery->whereIn('location_id', $accessibleLocationIds);
        }

        $customersCount = Customer::query()
            ->where('company_id', $company->id)
            ->count();

        $organizationsCount = Organization::query()
            ->where('company_id', $company->id)
            ->count();

        return response()->json([
            'company' => new CompanyResource(
                $company->loadMissing('address')
            ),
            'location' => $location
                ? new LocationResource($location->loadMissing('address'))
                : null,
            'scope' => [
                'company_id' => $company->id,
                'location_id' => $location?->id,
                'is_location_scoped' => $isLocationScoped,
            ],
            'metrics' => [
                'jobs_count' => $jobsQuery->count(),
                'customers_count' => $customersCount,
                'locations_count' => $isLocationScoped
                    ? 1
                    : $accessibleLocationIds->count(),
                'organizations_count' => $organizationsCount,
            ],
        ]);
    }
}
