<?php

namespace Database\Seeders;

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Models\User;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $fallbackPhoneNumber = 6025551000;

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
        // ORGANIZATION ADMINS (one per tenant)
        // =================================================================

        $orgAdminOverrides = [
            'Quick Dry Services' => [
                'name' => 'Org Admin',
                'email' => 'org.admin@quickdry.example',
                'phone' => '3855559999',
                'password' => '0rgAdm!n2026',
            ],
            'ABC Plumbing' => [
                'name' => 'Second Org Admin',
                'email' => 'org.admin@abcplumbing.example',
                'phone' => '8015551234',
                'password' => 'ABC0rgAdm!n2026',
            ],
        ];

        Organization::query()
            ->where('type', '!=', OrganizationType::Global->value)
            ->orderBy('id')
            ->get()
            ->each(function (Organization $organization) use (
                $orgAdminOverrides,
                $seedUser,
                &$fallbackPhoneNumber
            ): void {
                $defaultDomain = Str::of($organization->name)
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '')
                    ->toString().'.example';
                $domain = Str::after($organization->email ?? $defaultDomain, '@') ?: $defaultDomain;
                $config = $orgAdminOverrides[$organization->name] ?? [
                    'name' => "{$organization->name} Admin",
                    'email' => "org.admin@{$domain}",
                    'phone' => $organization->phone ?: (string) $fallbackPhoneNumber++,
                    'password' => '0rgAdm!n2026',
                ];

                $seedUser(
                    $organization,
                    $config['name'],
                    $config['email'],
                    $config['phone'],
                    $config['password'],
                    'org:admin'
                );
            });

        // =================================================================
        // EXTRA EXAMPLE ORG USERS
        // =================================================================

        $primaryOrg = $organization('Quick Dry Services');

        $seedUser($primaryOrg, 'Org Support', 'org.support@quickdry.example', '3855559998', '0rgSupp0rt!2026', 'org:support');
        $seedUser($primaryOrg, 'Org Read-Only', 'org.readonly@quickdry.example', '3855559997', '0rgRead0nly!2026', 'org:readonly');
    }
}
