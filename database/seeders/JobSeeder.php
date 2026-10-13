<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Location;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->orderBy('id')->get()->each(function (Company $company): void {
            Job::query()
                ->where('company_id', $company->id)
                ->get()
                ->each(function (Job $job): void {
                    $job->propertyAddress?->delete();
                    $job->delete();
                });

            $locations = Location::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $primaryLocation = $locations->first();
            $secondaryLocation = $locations->skip(1)->first() ?? $primaryLocation;

            $personalCustomer = Customer::query()
                ->where('company_id', $company->id)
                ->where('type', 'person')
                ->orderBy('id')
                ->first();

            $businessCustomer = Customer::query()
                ->where('company_id', $company->id)
                ->where('type', 'business')
                ->orderBy('id')
                ->first();

            if (! $primaryLocation || ! $personalCustomer || ! $businessCustomer) {
                return;
            }

            $personalAddress = Address::create([
                'name' => 'Jobsite',
                'line1' => '1840 Meadow View Drive',
                'line2' => null,
                'city' => 'Salt Lake City',
                'state' => 'UT',
                'postal_code' => '84121',
                'country' => 'US',
            ]);

            Job::create([
                'company_id' => $company->id,
                'location_id' => $primaryLocation->id,
                'customer_id' => $personalCustomer->id,
                'property_address_id' => $personalAddress->id,
                'reference_id' => 'JOB-'.str_pad((string) (($company->id * 10) + 1), 6, '0', STR_PAD_LEFT),
                'name' => "{$personalCustomer->name} - Emergency Dry Out",
                'status' => 'active',
                'type' => 'mitigation',
                'property_type' => 'residential',
                'loss_type' => 'water',
                'claim_number' => "CLM-{$company->id}01",
                'policy_number' => "POL-{$company->id}01",
                'opened_at' => now()->subDays(2),
                'date_of_loss' => now()->subDays(3)->toDateString(),
                'notes' => 'Sample residential mitigation job for local development.',
            ]);

            $businessAddress = Address::create([
                'name' => 'Jobsite',
                'line1' => '4100 South Highland Drive',
                'line2' => 'Building C',
                'city' => 'Millcreek',
                'state' => 'UT',
                'postal_code' => '84124',
                'country' => 'US',
            ]);

            Job::create([
                'company_id' => $company->id,
                'location_id' => $secondaryLocation->id,
                'customer_id' => $businessCustomer->id,
                'property_address_id' => $businessAddress->id,
                'reference_id' => 'JOB-'.str_pad((string) (($company->id * 10) + 2), 6, '0', STR_PAD_LEFT),
                'name' => "{$businessCustomer->name} - Common Area Rebuild",
                'status' => 'lead',
                'type' => 'reconstruction',
                'property_type' => 'commercial',
                'loss_type' => 'storm',
                'claim_number' => null,
                'policy_number' => null,
                'opened_at' => now()->subDay(),
                'date_of_loss' => now()->subDays(5)->toDateString(),
                'notes' => 'Sample commercial reconstruction job for local development.',
            ]);
        });
    }
}
