<?php

namespace Database\Seeders;

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Models\User;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Create development users for testing.
     * All phone numbers are stored in E.164 format.
     */
    public function run(): void
    {
        $phoneService = new PhoneService;

        // =================================================================
        // SYSTEM USERS (Built Ox Internal)
        // =================================================================

        // Create the global Built Ox organization that anchors system users.
        // The app must not depend on this being ID 1; tenancy checks use type and permissions.
        $builtOx = Organization::updateOrCreate(
            ['name' => 'Built Ox'],
            [
                'type' => OrganizationType::Global,
                'email' => 'admin@builtox.com',
                'phone' => $phoneService->formatToE164('4803091250'),
                'phone_country_code' => 'US',
            ]
        );

        // System Admin
        $user = User::factory()->create([
            'organization_id' => $builtOx->id,
            'name' => 'System Admin',
            'email' => 'admin@builtox.com',
            'phone' => $phoneService->formatToE164('4803091250'),
            'phone_country_code' => 'US',
            'password' => Hash::make('@dm!n$123'),
        ]);
        $user->assignRole('system:admin');

        // System Support
        $user = User::factory()->create([
            'organization_id' => $builtOx->id,
            'name' => 'Support User',
            'email' => 'support@builtox.com',
            'phone' => $phoneService->formatToE164('4803091251'),
            'phone_country_code' => 'US',
            'password' => Hash::make('support123'),
        ]);
        $user->assignRole('system:support');

        // System Readonly
        $user = User::factory()->create([
            'organization_id' => $builtOx->id,
            'name' => 'Readonly User',
            'email' => 'readonly@builtox.com',
            'phone' => $phoneService->formatToE164('4803091252'),
            'phone_country_code' => 'US',
            'password' => Hash::make('readonly123'),
        ]);
        $user->assignRole('system:readonly');

        // =================================================================
        // VENDOR ORGANIZATION (Example)
        // =================================================================

        $vendor = Organization::firstOrCreate(
            ['name' => 'ABC Plumbing'],
            [
                'type' => OrganizationType::Vendor,
                'email' => 'contact@abcplumbing.example',
                'phone' => $phoneService->formatToE164('8015551234'),
                'phone_country_code' => 'US',
            ]
        );

        // Vendor Admin
        $user = User::factory()->create([
            'organization_id' => $vendor->id,
            'name' => 'Vendor Admin',
            'email' => 'admin@abcplumbing.example',
            'phone' => $phoneService->formatToE164('8015551234'),
            'phone_country_code' => 'US',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('org:admin');

        // Vendor Member
        $user = User::factory()->create([
            'organization_id' => $vendor->id,
            'name' => 'Vendor Employee',
            'email' => 'employee@abcplumbing.example',
            'phone' => $phoneService->formatToE164('8015551235'),
            'phone_country_code' => 'US',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('org:member');

        // Vendor Viewer
        $user = User::factory()->create([
            'organization_id' => $vendor->id,
            'name' => 'Vendor Viewer',
            'email' => 'viewer@abcplumbing.example',
            'phone' => $phoneService->formatToE164('8015551236'),
            'phone_country_code' => 'US',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('org:viewer');

        // =================================================================
        // ANOTHER VENDOR ORGANIZATION (Example)
        // =================================================================

        $anotherVendor = Organization::firstOrCreate(
            ['name' => 'Quick Dry Services'],
            [
                'type' => OrganizationType::Vendor,
                'email' => 'contact@quickdry.example',
                'phone' => $phoneService->formatToE164('3855559999'),
                'phone_country_code' => 'US',
            ]
        );

        $user = User::factory()->create([
            'organization_id' => $anotherVendor->id,
            'name' => 'Another Vendor Admin',
            'email' => 'admin@quickdry.example',
            'phone' => $phoneService->formatToE164('3855559999'),
            'phone_country_code' => 'US',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('org:admin');
    }
}
