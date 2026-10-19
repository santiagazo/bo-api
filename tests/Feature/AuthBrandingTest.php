<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthBrandingTest extends TestCase
{
    public function test_registration_response_uses_the_configured_product_name(): void
    {
        config(['app.name' => 'jobTack']);

        $this->postJson('/api/register')
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'jobTack is invite-only. Ask an administrator to invite you.',
            ]);
    }
}
