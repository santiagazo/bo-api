<?php

namespace Database\Seeders;

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
        $organization = function (string $name): Organization {
            return Organization::query()
                ->where('name', $name)
                ->firstOrFail();
        };

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

        $builtOx = $organization('Built Ox');

        $seedUser($builtOx, 'Global Admin', 'global@builtox.com', '4803091250', '@dm!n$123', 'global:admin');
        $seedUser($builtOx, 'Global Support', 'global.support@builtox.com', '4803091251', 'Gl0balSupp0rt!2026', 'global:support');
        $seedUser($builtOx, 'Global Read-Only', 'global.readonly@builtox.com', '4803091252', 'Gl0balRead0nly!2026', 'global:readonly');

        // =================================================================
        // ORGANIZATION USERS (Tenant-scoped examples)
        // =================================================================

        $primaryOrg = $organization('Quick Dry Services');

        $seedUser($primaryOrg, 'Org Admin', 'org.admin@quickdry.example', '3855559999', '0rgAdm!n2026', 'org:admin');
        $seedUser($primaryOrg, 'Org Support', 'org.support@quickdry.example', '3855559998', '0rgSupp0rt!2026', 'org:support');
        $seedUser($primaryOrg, 'Org Read-Only', 'org.readonly@quickdry.example', '3855559997', '0rgRead0nly!2026', 'org:readonly');

        // =================================================================
        // SECOND TENANT (for cross-organization testing)
        // =================================================================

        $secondaryOrg = $organization('ABC Plumbing');

        $seedUser($secondaryOrg, 'Second Org Admin', 'org.admin@abcplumbing.example', '8015551234', 'ABC0rgAdm!n2026', 'org:admin');
    }
}
