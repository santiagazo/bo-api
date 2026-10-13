<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Resources\JobResource;
use App\Models\Address;
use App\Models\Company;
use App\Models\Job;
use App\Models\Location;
use App\Models\User;
use App\Support\CompanyContext;
use App\Support\LocationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Job::class);

        $company = app(CompanyContext::class)->company();
        $user = $request->user();
        $activeLocation = app(LocationContext::class)->location();

        $query = Job::query()
            ->with([
                'customer.primaryContact.primaryPhoneNumber',
                'customer.primaryContact.primaryEmailAddress',
                'customer.primaryAddress',
                'location',
                'propertyAddress',
            ])
            ->where('company_id', $company?->id);

        if ($activeLocation) {
            $query->where('location_id', $activeLocation->id);
        } elseif ($company && $user?->hasLocationRestrictions($company)) {
            $query->whereIn('location_id', $user->accessibleLocationIds($company));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', (int) $request->input('customer_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', (int) $request->input('location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($jobQuery) use ($search) {
                $jobQuery->where('name', 'ilike', "%{$search}%")
                    ->orWhere('reference_id', 'ilike', "%{$search}%")
                    ->orWhere('claim_number', 'ilike', "%{$search}%")
                    ->orWhere('policy_number', 'ilike', "%{$search}%")
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'ilike', "%{$search}%"));
            });
        }

        $jobs = $query
            ->orderByDesc('opened_at')
            ->orderByDesc('id')
            ->paginate(50);

        return JobResource::collection($jobs);
    }

    public function store(StoreJobRequest $request): JobResource
    {
        $company = app(CompanyContext::class)->company();
        $validated = $request->validated();

        $job = DB::transaction(function () use ($validated, $company, $request) {
            $propertyAddressData = $validated['property_address'];
            unset($validated['property_address']);

            $location = Location::query()->findOrFail($validated['location_id']);
            abort_unless(
                $request->user()?->canAccessLocation($location, $company),
                403,
                'You do not have access to the selected location.'
            );

            $this->guardLocationContext($location->id);

            $address = Address::create($propertyAddressData);

            $job = Job::create([
                ...$validated,
                'company_id' => $company->id,
                'property_address_id' => $address->id,
                'opened_at' => isset($validated['opened_at'])
                    ? Carbon::parse($validated['opened_at'])
                    : now(),
            ]);

            if (blank($job->reference_id)) {
                $job->update([
                    'reference_id' => $this->generatedReferenceId($job),
                ]);
            }

            return $job;
        });

        return $this->resource($job->fresh());
    }

    public function show(Request $request, Job $job): JobResource
    {
        $this->authorize('view', $job);
        $this->guardJobVisibility($request->user(), $job);

        return $this->resource($job);
    }

    public function update(UpdateJobRequest $request, Job $job): JobResource
    {
        $this->authorize('update', $job);
        $this->guardJobVisibility($request->user(), $job);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $job, $request) {
            if (isset($validated['location_id'])) {
                $location = Location::query()->findOrFail($validated['location_id']);

                abort_unless(
                    $request->user()?->canAccessLocation($location, $job->company),
                    403,
                    'You do not have access to the selected location.'
                );
            }

            if (isset($validated['property_address'])) {
                $propertyAddressData = $validated['property_address'];
                unset($validated['property_address']);

                if ($job->property_address_id && $job->propertyAddress) {
                    $job->propertyAddress->update($propertyAddressData);
                } else {
                    $address = Address::create($propertyAddressData);
                    $validated['property_address_id'] = $address->id;
                }
            }

            if (array_key_exists('opened_at', $validated) && $validated['opened_at']) {
                $validated['opened_at'] = Carbon::parse($validated['opened_at']);
            }

            if ($validated !== []) {
                $job->update($validated);
            }

            $this->guardLocationContext($job->location_id);
        });

        return $this->resource($job->fresh());
    }

    public function destroy(Request $request, Job $job): JsonResponse
    {
        $this->authorize('delete', $job);
        $this->guardJobVisibility($request->user(), $job);

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully.']);
    }

    protected function resource(Job $job): JobResource
    {
        return new JobResource(
            $job->load([
                'customer.primaryContact.primaryPhoneNumber',
                'customer.primaryContact.primaryEmailAddress',
                'customer.primaryAddress',
                'location',
                'propertyAddress',
            ])
        );
    }

    protected function generatedReferenceId(Job $job): string
    {
        return 'JOB-'.str_pad((string) $job->id, 6, '0', STR_PAD_LEFT);
    }

    protected function guardJobVisibility(?User $user, Job $job): void
    {
        abort_unless($user, 401);

        $activeLocation = app(LocationContext::class)->location();

        if (! $activeLocation) {
            $this->guardUserLocationAccess($user, $job->company, $job->location_id);

            return;
        }

        abort_unless(
            $job->location_id === $activeLocation->id,
            403,
            'The requested job does not belong to the active location context.'
        );
    }

    protected function guardLocationContext(int $locationId): void
    {
        $activeLocation = app(LocationContext::class)->location();

        if (! $activeLocation) {
            return;
        }

        abort_unless(
            $locationId === $activeLocation->id,
            403,
            'The selected location does not match the active location context.'
        );
    }

    protected function guardUserLocationAccess(User $user, Company $company, int $locationId): void
    {
        if (! $user->hasLocationRestrictions($company)) {
            return;
        }

        abort_unless(
            $user->accessibleLocationIds($company)->contains($locationId),
            403,
            'You do not have access to the requested location.'
        );
    }
}
