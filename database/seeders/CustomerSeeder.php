<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $phoneService = new PhoneService;

        Company::query()->orderBy('id')->get()->each(function (Company $company) use ($phoneService): void {
            $personalCustomer = Customer::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => 'Jordan Parker',
                ],
                [
                    'type' => 'person',
                    'notes' => 'Sample residential customer for local development.',
                    'is_active' => true,
                ]
            );

            $personalCustomer->addresses()->delete();
            $personalCustomer->addresses()->create([
                'type' => 'mailing',
                'address1' => '1840 Meadow View Drive',
                'city' => 'Salt Lake City',
                'state' => 'UT',
                'postal_code' => '84121',
                'country' => 'US',
                'is_primary' => true,
                'is_active' => true,
            ]);

            foreach ($personalCustomer->contacts as $contact) {
                $contact->phoneNumbers()->delete();
                $contact->emailAddresses()->delete();
            }
            $personalCustomer->contacts()->delete();

            $primaryPersonalContact = $personalCustomer->contacts()->create([
                'name' => 'Jordan Parker',
                'title' => null,
                'is_primary' => true,
                'is_active' => true,
            ]);

            $primaryPersonalContact->phoneNumbers()->create([
                'type' => 'mobile',
                'phone_number' => $phoneService->formatToE164('8015550101'),
                'phone_country_code' => 'US',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $primaryPersonalContact->emailAddresses()->create([
                'type' => 'primary',
                'email' => 'jordan.parker@example.com',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $businessCustomer = Customer::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => 'Summit Crest Property Management',
                ],
                [
                    'type' => 'business',
                    'notes' => 'Sample commercial customer with more than one contact.',
                    'is_active' => true,
                ]
            );

            $businessCustomer->addresses()->delete();
            $businessCustomer->addresses()->createMany([
                [
                    'type' => 'mailing',
                    'address1' => '4100 South Highland Drive',
                    'address2' => 'Suite 240',
                    'city' => 'Millcreek',
                    'state' => 'UT',
                    'postal_code' => '84124',
                    'country' => 'US',
                    'is_primary' => true,
                    'is_active' => true,
                ],
                [
                    'type' => 'billing',
                    'address1' => 'PO Box 2147',
                    'city' => 'Salt Lake City',
                    'state' => 'UT',
                    'postal_code' => '84110',
                    'country' => 'US',
                    'is_primary' => false,
                    'is_active' => true,
                ],
            ]);

            foreach ($businessCustomer->contacts as $contact) {
                $contact->phoneNumbers()->delete();
                $contact->emailAddresses()->delete();
            }
            $businessCustomer->contacts()->delete();

            $officeContact = $businessCustomer->contacts()->create([
                'name' => 'Summit Crest Front Desk',
                'title' => 'Office',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $officeContact->phoneNumbers()->create([
                'type' => 'work',
                'phone_number' => $phoneService->formatToE164('8015550200'),
                'phone_country_code' => 'US',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $officeContact->emailAddresses()->create([
                'type' => 'primary',
                'email' => 'office@summitcrestpm.example',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $projectContact = $businessCustomer->contacts()->create([
                'name' => 'Avery Monroe',
                'title' => 'Property Manager',
                'is_primary' => false,
                'is_active' => true,
            ]);

            $projectContact->phoneNumbers()->create([
                'type' => 'mobile',
                'phone_number' => $phoneService->formatToE164('8015550201'),
                'phone_country_code' => 'US',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $projectContact->emailAddresses()->create([
                'type' => 'primary',
                'email' => 'avery.monroe@summitcrestpm.example',
                'is_primary' => true,
                'is_active' => true,
            ]);
        });
    }
}
