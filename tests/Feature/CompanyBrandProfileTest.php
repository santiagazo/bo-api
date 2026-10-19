<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyBrandProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_owns_one_brand_profile(): void
    {
        $company = Company::create([
            'name' => 'Example Service Company',
            'slug' => 'example-service-company',
            'status' => 'active',
            'timezone' => 'America/Denver',
            'currency_code' => 'USD',
            'default_locale' => 'en-US',
        ]);

        $company->brandProfile()->create([
            'primary_color' => '#083A68',
            'university_name' => 'Example University',
        ]);

        $company->refresh();

        $this->assertSame('#083A68', $company->brandProfile->primary_color);
        $this->assertSame('Example University', $company->brandProfile->university_name);
        $this->assertTrue($company->brandProfile->company->is($company));
    }
}
