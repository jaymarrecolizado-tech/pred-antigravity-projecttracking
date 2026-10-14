<?php

namespace Tests\Unit;

use App\Domain\Access\GrantResolver;
use App\Domain\Access\RoleGrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrantResolverTest extends TestCase
{
    use RefreshDatabase;

    protected GrantResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new GrantResolver();
    }

    public function test_super_admin_allows_any_action_and_location(): void
    {
        $admin = User::factory()->create();
        $admin->roleGrants()->create([
            'role' => GrantResolver::ROLE_SUPER_ADMIN,
        ]);

        $this->assertTrue($this->resolver->allows($admin, 'any.unlisted.ability', 1, 1, '021528'));
        $this->assertTrue($this->resolver->allows($admin, 'project.delete', 99, 5, '023100'));
    }

    public function test_hierarchical_psgc_prefix_matching(): void
    {
        // Manager granted on Cagayan Province (0215)
        $manager = User::factory()->create();
        $manager->roleGrants()->create([
            'role' => GrantResolver::ROLE_PROJECT_MANAGER,
            'psgc_code' => '0215',
        ]);

        // Tuguegarao City (021528) and its barangay (021528001) are inside Cagayan (0215)
        $this->assertTrue($this->resolver->allows($manager, 'project.update', 1, null, '021528'));
        $this->assertTrue($this->resolver->allows($manager, 'project.update', 1, null, '021528001'));

        // Ilagan City (023114) is in Isabela (0231), NOT Cagayan (0215)
        $this->assertFalse($this->resolver->allows($manager, 'project.update', 1, null, '023114'));
    }

    public function test_project_id_scoping(): void
    {
        // Encoder restricted to Project #10
        $encoder = User::factory()->create();
        $encoder->roleGrants()->create([
            'role' => GrantResolver::ROLE_ENCODER,
            'project_id' => 10,
        ]);

        // Allowed on Project 10
        $this->assertTrue($this->resolver->allows($encoder, 'submission.create', 10));

        // Denied on Project 12
        $this->assertFalse($this->resolver->allows($encoder, 'submission.create', 12));
    }

    public function test_privileged_roles_require_two_factor(): void
    {
        $admin = User::factory()->create();
        $admin->roleGrants()->create(['role' => GrantResolver::ROLE_SUPER_ADMIN]);

        $pm = User::factory()->create();
        $pm->roleGrants()->create(['role' => GrantResolver::ROLE_PROJECT_MANAGER]);

        $reviewer = User::factory()->create();
        $reviewer->roleGrants()->create(['role' => GrantResolver::ROLE_REVIEWER]);

        $encoder = User::factory()->create();
        $encoder->roleGrants()->create(['role' => GrantResolver::ROLE_ENCODER]);

        $this->assertTrue($this->resolver->requiresTwoFactor($admin));
        $this->assertTrue($this->resolver->requiresTwoFactor($pm));
        $this->assertTrue($this->resolver->requiresTwoFactor($reviewer));
        $this->assertFalse($this->resolver->requiresTwoFactor($encoder));
    }
}
