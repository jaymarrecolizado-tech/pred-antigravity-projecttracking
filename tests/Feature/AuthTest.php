<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_user_can_authenticate_and_reach_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'officer@dict.gov.ph',
            'password' => Hash::make('SecretPass123!'),
            'two_factor_secret' => 'SECRET_KEY_123',
            'two_factor_confirmed_at' => now(),
        ]);
        $user->roleGrants()->create(['role' => GrantResolver::ROLE_SUPER_ADMIN]);

        $response = $this->post('/login', [
            'email' => 'officer@dict.gov.ph',
            'password' => 'SecretPass123!',
        ]);

        // User with 2FA is directed to 2FA challenge
        $response->assertRedirect('/two-factor-challenge');

        // Accessing dashboard when session established returns HTTP 200
        $dashboardResponse = $this->actingAs($user)->get('/');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_non_two_factor_user_authenticates_directly_to_dashboard(): void
    {
        $encoder = User::factory()->create([
            'email' => 'encoder@dict.gov.ph',
            'password' => Hash::make('SecretPass123!'),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $encoder->roleGrants()->create(['role' => GrantResolver::ROLE_ENCODER]);

        $response = $this->post('/login', [
            'email' => 'encoder@dict.gov.ph',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($encoder);
    }

    public function test_unconfigured_privileged_user_is_redirected_to_two_factor_setup(): void
    {
        // Privileged user without two_factor_confirmed_at
        $user = User::factory()->create([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $user->roleGrants()->create(['role' => GrantResolver::ROLE_PROJECT_MANAGER]);

        // Attempting to access dashboard
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect(route('two-factor.setup'));

        // Can access the 2FA setup page without loop
        $setupResponse = $this->actingAs($user)->get(route('two-factor.setup'));
        $setupResponse->assertStatus(200);
        $setupResponse->assertInertia(fn ($page) => $page->component('Auth/TwoFactorSetup'));
    }

    public function test_admin_can_view_users_and_create_grants(): void
    {
        $admin = User::factory()->create([
            'two_factor_secret' => 'SECRET_KEY_123',
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->roleGrants()->create(['role' => GrantResolver::ROLE_SUPER_ADMIN]);

        $targetUser = User::factory()->create(['name' => 'Field Encoder Juan']);

        // View users
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Users/Index'));

        // Assign grant
        $grantResponse = $this->actingAs($admin)->post(route('admin.users.grants.store', $targetUser), [
            'role' => GrantResolver::ROLE_ENCODER,
            'psgc_code' => '0215',
        ]);

        $grantResponse->assertSessionHas('success');
        $this->assertDatabaseHas('role_grants', [
            'user_id' => $targetUser->id,
            'role' => GrantResolver::ROLE_ENCODER,
            'psgc_code' => '0215',
        ]);
    }
}
