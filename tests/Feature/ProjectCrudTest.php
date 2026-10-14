<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $projectManager;
    protected User $encoder;
    protected ProjectType $projectType;
    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        // 2FA secret and confirmed at are required for privileged roles
        $this->admin = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => 'SECRET123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->roleGrants()->create(['role' => GrantResolver::ROLE_SUPER_ADMIN]);

        $this->projectManager = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => 'SECRET456',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->projectManager->roleGrants()->create(['role' => GrantResolver::ROLE_PROJECT_MANAGER]);

        $this->encoder = User::factory()->create([
            'is_active' => true,
        ]);
        $this->encoder->roleGrants()->create(['role' => GrantResolver::ROLE_ENCODER]);

        $this->projectType = ProjectType::create([
            'name' => 'Site Deployment',
            'kind' => 'site',
            'marker_shape' => 'circle',
            'color_code' => '#2563eb',
        ]);

        $this->program = Program::create([
            'name' => 'Free Wi-Fi for All',
            'owner_unit' => 'NIPPSB',
            'color' => '#2563eb',
        ]);
    }

    public function test_authenticated_user_can_view_project_directory(): void
    {
        $response = $this->actingAs($this->projectManager)
            ->get('/projects');

        $response->assertStatus(200);
    }

    public function test_encoder_is_forbidden_from_creating_projects(): void
    {
        $response = $this->actingAs($this->encoder)
            ->get('/projects/create');

        $response->assertStatus(403);
    }

    public function test_project_manager_can_create_project_with_scopes_and_targets(): void
    {
        $payload = [
            'code' => 'FW4A-TEST-001',
            'name' => 'Free Wi-Fi Test Project',
            'program_id' => $this->program->id,
            'project_type_id' => $this->projectType->id,
            'lifecycle' => 'active',
            'budget' => 5000000.00,
            'funding_source' => 'GAA 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'scopes' => ['0215000000', '0231000000'],
            'targets' => [
                [
                    'indicator_key' => 'operational_hotspots',
                    'period' => '2026',
                    'psgc_code' => null,
                    'target_value' => 50,
                ],
            ],
        ];

        $response = $this->actingAs($this->projectManager)
            ->post('/projects', $payload);

        $response->assertRedirect('/projects');
        $this->assertDatabaseHas('projects', ['code' => 'FW4A-TEST-001']);
        $this->assertDatabaseHas('project_scopes', ['psgc_code' => '0215000000']);
        $this->assertDatabaseHas('indicator_targets', [
            'indicator_key' => 'operational_hotspots',
            'period' => '2026',
            'target_value' => 50.00,
        ]);
    }

    public function test_super_admin_can_manage_project_types_and_status_definitions(): void
    {
        // 1. Visit Project Types
        $res = $this->actingAs($this->admin)->get('/admin/project-types');
        $res->assertStatus(200);

        // 2. Add Status Definition
        $statusPayload = [
            'key' => 'testing_ready',
            'label' => 'Testing Ready',
            'sort_order' => 5,
            'is_completed' => true,
            'is_terminal' => false,
            'next_keys' => [],
        ];

        $postRes = $this->actingAs($this->admin)
            ->post("/admin/project-types/{$this->projectType->id}/statuses", $statusPayload);

        $postRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('status_defs', [
            'project_type_id' => $this->projectType->id,
            'key' => 'testing_ready',
            'is_completed' => true,
        ]);

        // 3. Add Custom Field Definition
        $fieldPayload = [
            'key' => 'contractor_name',
            'label' => 'Contractor Name',
            'type' => 'text',
            'required' => true,
            'applies_to' => 'site',
            'sort_order' => 1,
        ];

        $fieldRes = $this->actingAs($this->admin)
            ->post("/admin/project-types/{$this->projectType->id}/fields", $fieldPayload);

        $fieldRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('field_defs', [
            'project_type_id' => $this->projectType->id,
            'key' => 'contractor_name',
            'required' => true,
        ]);
    }
}
