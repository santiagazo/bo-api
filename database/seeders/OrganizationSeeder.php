<?php

namespace Database\Seeders;

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Seed the application's organizations.
     */
    public function run(): void
    {
        $phoneService = new PhoneService;

        foreach ($this->organizations() as $organization) {
            Organization::updateOrCreate(
                ['name' => $organization['name']],
                [
                    'type' => $organization['type'],
                    'email' => $organization['email'],
                    'phone' => $phoneService->formatToE164($organization['phone']),
                    'phone_country_code' => 'US',
                ]
            );
        }
    }

    /**
     * Development organization catalog.
     */
    protected function organizations(): array
    {
        return [
            [
                'name' => 'Built Ox',
                'type' => OrganizationType::Global,
                'email' => 'global@builtox.com',
                'phone' => '4803091250',
            ],
            [
                'name' => 'Quick Dry Services',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@quickdry.example',
                'phone' => '3855559999',
            ],
            [
                'name' => 'ABC Plumbing',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@abcplumbing.example',
                'phone' => '8015551234',
            ],
            [
                'name' => 'Built Ox Water Damage Pros',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@builtoxwaterdamagepros.example',
                'phone' => '6025550147',
            ],
            [
                'name' => 'The Dry Guys',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@thedryguys.example',
                'phone' => '6025550148',
            ],
            [
                'name' => 'Moist Busters',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@moistbusters.example',
                'phone' => '6025550149',
            ],
            [
                'name' => 'Water You Waiting For Resto',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@wateryouwaitingforresto.example',
                'phone' => '6025550150',
            ],
            [
                'name' => 'Soggy Bottom Fixers',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@soggybottomfixers.example',
                'phone' => '6025550151',
            ],
            [
                'name' => 'The Mold Standard',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@themoldstandard.example',
                'phone' => '6025550152',
            ],
            [
                'name' => 'Dry Me a River Restoration',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@drymeariverrestoration.example',
                'phone' => '6025550153',
            ],
            [
                'name' => 'Damp Detectives',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@dampdetectives.example',
                'phone' => '6025550154',
            ],
            [
                'name' => 'Soak It Up Services',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@soakitupservices.example',
                'phone' => '6025550155',
            ],
            [
                'name' => 'The Restoration Nation',
                'type' => OrganizationType::Vendor,
                'email' => 'contact@therestorationnation.example',
                'phone' => '6025550156',
            ],
        ];
    }
}
