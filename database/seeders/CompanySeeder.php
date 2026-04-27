<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $phoneService = new PhoneService;

        foreach ($this->companies() as $company) {
            Company::updateOrCreate(
                ['slug' => $company['slug']],
                [
                    'name' => $company['name'],
                    'legal_name' => $company['legal_name'] ?? null,
                    'status' => 'active',
                    'primary_email' => $company['primary_email'],
                    'primary_phone' => $phoneService->formatToE164($company['primary_phone']),
                    'phone_country_code' => 'US',
                    'timezone' => 'America/Denver',
                    'currency_code' => 'USD',
                    'default_locale' => 'en-US',
                ]
            );
        }
    }

    protected function companies(): array
    {
        return [
            [
                'name' => 'Built Ox Water Damage Pros',
                'legal_name' => 'Built Ox Water Damage Pros LLC',
                'slug' => 'built-ox-water-damage-pros',
                'primary_email' => 'contact@builtoxwaterdamagepros.example',
                'primary_phone' => '6025550147',
            ],
            [
                'name' => 'Quick Dry Services',
                'slug' => 'quick-dry-services',
                'primary_email' => 'contact@quickdry.example',
                'primary_phone' => '3855559999',
            ],
            [
                'name' => 'The Dry Guys',
                'slug' => 'the-dry-guys',
                'primary_email' => 'contact@thedryguys.example',
                'primary_phone' => '6025550148',
            ],
            [
                'name' => 'Moist Busters',
                'slug' => 'moist-busters',
                'primary_email' => 'contact@moistbusters.example',
                'primary_phone' => '6025550149',
            ],
            [
                'name' => 'Water You Waiting For Resto',
                'slug' => 'water-you-waiting-for-resto',
                'primary_email' => 'contact@wateryouwaitingforresto.example',
                'primary_phone' => '6025550150',
            ],
            [
                'name' => 'Soggy Bottom Fixers',
                'slug' => 'soggy-bottom-fixers',
                'primary_email' => 'contact@soggybottomfixers.example',
                'primary_phone' => '6025550151',
            ],
            [
                'name' => 'The Mold Standard',
                'slug' => 'the-mold-standard',
                'primary_email' => 'contact@themoldstandard.example',
                'primary_phone' => '6025550152',
            ],
            [
                'name' => 'Dry Me a River Restoration',
                'slug' => 'dry-me-a-river-restoration',
                'primary_email' => 'contact@drymeariverrestoration.example',
                'primary_phone' => '6025550153',
            ],
            [
                'name' => 'Damp Detectives',
                'slug' => 'damp-detectives',
                'primary_email' => 'contact@dampdetectives.example',
                'primary_phone' => '6025550154',
            ],
            [
                'name' => 'Soak It Up Services',
                'slug' => 'soak-it-up-services',
                'primary_email' => 'contact@soakitupservices.example',
                'primary_phone' => '6025550155',
            ],
            [
                'name' => 'The Restoration Nation',
                'slug' => 'the-restoration-nation',
                'primary_email' => 'contact@therestorationnation.example',
                'primary_phone' => '6025550156',
            ],
        ];
    }
}
