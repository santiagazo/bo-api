<?php

namespace Database\Seeders;

use App\Enums\MembershipScope;
use App\Enums\MembershipStatus;
use App\Models\Company;
use App\Models\Membership;
use App\Models\User;
use App\Services\PhoneService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $phoneService = new PhoneService;

        $seedUser = function (
            string $name,
            string $email,
            string $phone,
            string $password
        ) use ($phoneService): User {
            return User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phoneService->formatToE164($phone),
                    'phone_country_code' => 'US',
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
        };

        $attachMembership = function (
            User $user,
            MembershipScope $scope,
            ?int $companyId,
            bool $isPrimary,
            array $roles
        ): void {
            $membership = Membership::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'scope_type' => $scope,
                    'company_id' => $companyId,
                ],
                [
                    'status' => MembershipStatus::Active,
                    'is_primary' => $isPrimary,
                ]
            );

            $membership->syncRoles($roles);
        };

        $platformAdmin = $seedUser('Platform Admin', 'global@builtox.com', '4803091250', 'Gl0bal@dm!n$123');
        $platformSupport = $seedUser('Platform Support', 'global.support@builtox.com', '4803091251', 'Gl0balSupp0rt!2026');
        $platformReadonly = $seedUser('Platform Read-Only', 'global.readonly@builtox.com', '4803091252', 'Gl0balRead0nly!2026');

        $attachMembership($platformAdmin, MembershipScope::Platform, null, false, ['platform:admin']);
        $attachMembership($platformSupport, MembershipScope::Platform, null, false, ['platform:support']);
        $attachMembership($platformReadonly, MembershipScope::Platform, null, false, ['platform:readonly']);

        Company::query()->orderBy('id')->get()->each(function (Company $company) use ($seedUser, $attachMembership): void {
            $slug = Str::of($company->slug)->replace('-', '')->toString();
            $domain = "{$slug}.example";

            $admin = $seedUser(
                "{$company->name} Admin",
                "admin@{$domain}",
                $company->primary_phone ?? '6025551000',
                'C0mpanyAdm!n2026'
            );

            $support = $seedUser(
                "{$company->name} Support",
                "support@{$domain}",
                '6025552000',
                'C0mpanySupp0rt!2026'
            );

            $readonly = $seedUser(
                "{$company->name} Read-Only",
                "readonly@{$domain}",
                '6025553000',
                'C0mpanyRead0nly!2026'
            );

            $attachMembership($admin, MembershipScope::Company, $company->id, true, ['company:admin']);
            $attachMembership($support, MembershipScope::Company, $company->id, true, ['company:support']);
            $attachMembership($readonly, MembershipScope::Company, $company->id, true, ['company:readonly']);
        });
    }
}
