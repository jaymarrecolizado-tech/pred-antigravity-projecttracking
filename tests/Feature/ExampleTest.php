<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = \App\Models\User::factory()->create([
            'two_factor_secret' => 'SECRET_KEY_123',
            'two_factor_confirmed_at' => now(),
        ]);
        $user->roleGrants()->create(['role' => \App\Domain\Access\GrantResolver::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('Dashboard')
        );
    }
}
