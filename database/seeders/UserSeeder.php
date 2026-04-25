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
        $seedUser = function (
            Organization $organization,
            string $name,
            string $email,
            string $phone,
            string $password,
            string $role
        ) use ($phoneService): User {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'organization_id' => $organization->id,
                    'name' => $name,
                    'phone' => $phoneService->formatToE164($phone),
                    'phone_country_code' => 'US',
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role]);

            return $user;
        };

        // =================================================================
        // GLOBAL USERS (Built Ox Internal)
        // =================================================================

        // Create the global Built Ox organization that anchors global users.
        // The app must not depend on this being ID 1; tenancy checks use type and permissions.
        $builtOx = Organization::updateOrCreate(
            ['name' => 'Built Ox'],
            [
                'type' => OrganizationType::Global,
                'email' => 'global@builtox.com',
                'phone' => $phoneService->formatToE164('4803091250'),
                'phone_country_code' => 'US',
            ]
        );

        $seedUser($builtOx, 'Global Admin', 'global@builtox.com', '4803091250', '@dm!n$123', 'global:admin');
        $seedUser($builtOx, 'Global Support', 'global.support@builtox.com', '4803091251', 'Gl0balSupp0rt!2026', 'global:support');
        $seedUser($builtOx, 'Global Read-Only', 'global.readonly@builtox.com', '4803091252', 'Gl0balRead0nly!2026', 'global:readonly');

        // =================================================================
        // ORGANIZATION USERS (Tenant-scoped examples)
        // =================================================================

        $primaryOrg = Organization::updateOrCreate(
            ['name' => 'Quick Dry Services'],
            [
                'type' => OrganizationType::Vendor,
                'email' => 'contact@quickdry.example',
                'phone' => $phoneService->formatToE164('3855559999'),
                'phone_country_code' => 'US',
            ]
        );

        $seedUser($primaryOrg, 'Org Admin', 'org.admin@quickdry.example', '3855559999', '0rgAdm!n2026', 'org:admin');
        $seedUser($primaryOrg, 'Org Support', 'org.support@quickdry.example', '3855559998', '0rgSupp0rt!2026', 'org:support');
        $seedUser($primaryOrg, 'Org Read-Only', 'org.readonly@quickdry.example', '3855559997', '0rgRead0nly!2026', 'org:readonly');

        // =================================================================
        // SECOND TENANT (for cross-organization testing)
        // =================================================================

        $secondaryOrg = Organization::updateOrCreate(
            ['name' => 'ABC Plumbing'],
            [
                'type' => OrganizationType::Vendor,
                'email' => 'contact@abcplumbing.example',
                'phone' => $phoneService->formatToE164('8015551234'),
                'phone_country_code' => 'US',
            ]
        );

        $seedUser($secondaryOrg, 'Second Org Admin', 'org.admin@abcplumbing.example', '8015551234', 'ABC0rgAdm!n2026', 'org:admin');
    }
}
