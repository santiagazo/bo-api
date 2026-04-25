<?php

namespace Tests\Feature;

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_user_can_assume_an_organization_from_request_context(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $builtOx = $this->organization('Built Ox', OrganizationType::Global);
        $vendor = $this->organization('Vendor One', OrganizationType::Vendor);

        $admin = User::factory()->create(['organization_id' => $builtOx->id]);
        $admin->assignRole('global:admin');

        $response = $this
            ->actingAs($admin)
            ->withHeader('X-Organization-Id', (string) $vendor->id)
            ->getJson('/api/organizations/current');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $vendor->id);
    }

    public function test_global_permission_without_global_org_membership_cannot_cross_tenants(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $ownOrg = $this->organization('Vendor One', OrganizationType::Vendor);
        $otherOrg = $this->organization('Vendor Two', OrganizationType::Vendor);

        $user = User::factory()->create(['organization_id' => $ownOrg->id]);
        $user->assignRole('global:admin');

        $response = $this
            ->actingAs($user)
            ->withHeader('X-Organization-Id', (string) $otherOrg->id)
            ->getJson('/api/organizations/current');

        $response->assertForbidden();
    }

    public function test_organization_user_cannot_assume_a_different_organization(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $ownOrg = $this->organization('Vendor One', OrganizationType::Vendor);
        $otherOrg = $this->organization('Vendor Two', OrganizationType::Vendor);

        $user = User::factory()->create(['organization_id' => $ownOrg->id]);
        $user->assignRole('org:admin');

        $response = $this
            ->actingAs($user)
            ->withHeader('X-Organization-Id', (string) $otherOrg->id)
            ->getJson('/api/organizations/current');

        $response->assertForbidden();
    }

    public function test_user_index_defaults_to_the_current_organization(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $ownOrg = $this->organization('Vendor One', OrganizationType::Vendor);
        $otherOrg = $this->organization('Vendor Two', OrganizationType::Vendor);

        $user = User::factory()->create([
            'name' => 'Own User',
            'organization_id' => $ownOrg->id,
        ]);
        $user->assignRole('org:admin');

        User::factory()->create([
            'name' => 'Other User',
            'organization_id' => $otherOrg->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/users');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Own User');
    }

    protected function organization(string $name, OrganizationType $type): Organization
    {
        return Organization::query()->create([
            'name' => $name,
            'type' => $type,
        ]);
    }
}
