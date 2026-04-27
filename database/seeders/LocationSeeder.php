<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Company;
use App\Models\Location;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $phoneService = new PhoneService;

        Company::query()->orderBy('id')->get()->each(function (Company $company, int $index) use ($phoneService): void {
            foreach ($this->locationsForCompany($company, $index) as $locationData) {
                $addressData = $locationData['address'];
                unset($locationData['address']);

                $location = Location::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $locationData['name'],
                    ],
                    [
                        ...$locationData,
                        'phone' => $phoneService->formatToE164($locationData['phone'], $locationData['phone_country_code']),
                    ]
                );

                if ($location->address) {
                    $location->address->update($addressData);
                } else {
                    $address = Address::create($addressData);
                    $location->update(['address_id' => $address->id]);
                }
            }
        });
    }

    protected function locationsForCompany(Company $company, int $index): array
    {
        $cities = [
            ['city' => 'Phoenix', 'state' => 'AZ', 'postal_code' => '85004'],
            ['city' => 'Mesa', 'state' => 'AZ', 'postal_code' => '85201'],
            ['city' => 'Tempe', 'state' => 'AZ', 'postal_code' => '85281'],
            ['city' => 'Scottsdale', 'state' => 'AZ', 'postal_code' => '85251'],
            ['city' => 'Chandler', 'state' => 'AZ', 'postal_code' => '85225'],
            ['city' => 'Gilbert', 'state' => 'AZ', 'postal_code' => '85233'],
        ];
        $city = $cities[$index % count($cities)];
        $baseStreetNumber = 1000 + ($company->id * 37);

        $locations = [
            [
                'company_id' => $company->id,
                'name' => 'Main Office',
                'code' => 'HQ',
                'phone' => $company->primary_phone ?? '6025550000',
                'phone_country_code' => $company->phone_country_code ?? 'US',
                'email' => $company->primary_email,
                'status' => 'active',
                'timezone' => $company->timezone ?? 'America/Denver',
                'address' => [
                    'name' => "{$company->name} Main Office",
                    'line1' => "{$baseStreetNumber} Restoration Way",
                    'line2' => null,
                    ...$city,
                    'country' => 'US',
                ],
            ],
        ];

        if (in_array($company->slug, ['built-ox-water-damage-pros', 'quick-dry-services', 'the-restoration-nation'], true)) {
            $locations[] = [
                'company_id' => $company->id,
                'name' => 'Equipment Yard',
                'code' => 'YARD',
                'phone' => $company->primary_phone ?? '6025550000',
                'phone_country_code' => $company->phone_country_code ?? 'US',
                'email' => preg_replace('/^contact@/', 'yard@', $company->primary_email ?? 'operations@example.com'),
                'status' => 'active',
                'timezone' => $company->timezone ?? 'America/Denver',
                'address' => [
                    'name' => "{$company->name} Equipment Yard",
                    'line1' => ($baseStreetNumber + 18) . ' Drydown Ave',
                    'line2' => null,
                    'city' => $city['city'],
                    'state' => $city['state'],
                    'postal_code' => $city['postal_code'],
                    'country' => 'US',
                ],
            ];
        }

        return $locations;
    }
}
