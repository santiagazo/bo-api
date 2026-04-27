<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Organization;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $phoneService = new PhoneService;

        Company::query()->orderBy('id')->get()->each(function (Company $company) use ($phoneService): void {
            $carrier = Organization::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'display_name' => 'Statewide Mutual Insurance',
                ],
                [
                    'status' => 'active',
                    'primary_email' => 'claims@statewide-mutual.example',
                    'primary_phone' => $phoneService->formatToE164('6025551100'),
                    'phone_country_code' => 'US',
                ]
            );

            $carrier->typeAssignments()->delete();
            $carrier->typeAssignments()->createMany([
                ['type' => 'insurance_carrier', 'is_primary' => true],
            ]);

            $vendor = Organization::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'display_name' => 'ABC Plumbing',
                ],
                [
                    'status' => 'active',
                    'primary_email' => 'dispatch@abcplumbing.example',
                    'primary_phone' => $phoneService->formatToE164('8015551234'),
                    'phone_country_code' => 'US',
                ]
            );

            $vendor->typeAssignments()->delete();
            $vendor->typeAssignments()->createMany([
                ['type' => 'vendor', 'is_primary' => true],
                ['type' => 'subcontractor', 'is_primary' => false],
            ]);

            $partner = Organization::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'display_name' => 'Mold Detection Pros',
                ],
                [
                    'status' => 'active',
                    'primary_email' => 'office@molddetectionpros.example',
                    'primary_phone' => $phoneService->formatToE164('6025551200'),
                    'phone_country_code' => 'US',
                ]
            );

            $partner->typeAssignments()->delete();
            $partner->typeAssignments()->createMany([
                ['type' => 'referral_partner', 'is_primary' => true],
                ['type' => 'vendor', 'is_primary' => false],
            ]);
        });
    }
}
