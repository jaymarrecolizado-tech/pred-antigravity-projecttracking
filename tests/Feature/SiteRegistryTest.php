<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Institution;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $projectManager;
    protected User $encoder;
    protected Project $project;
    protected ProjectType $projectType;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->encoder = User::factory()->create(['is_active' => true]);
        $this->encoder->roleGrants()->create(['role' => GrantResolver::ROLE_ENCODER]);

        $this->projectType = ProjectType::create([
            'name' => 'Site Deployment',
            'kind' => 'site',
            'marker_shape' => 'circle',
            'color_code' => '#2563eb',
        ]);

        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'planned',
            'label' => 'Planned',
            'sort_order' => 1,
            'is_completed' => false,
        ]);

        $program = Program::create([
            'name' => 'Free Wi-Fi for All',
            'owner_unit' => 'NIPPSB',
            'color' => '#2563eb',
        ]);

        $this->project = Project::create([
            'code' => 'FW4A-TEST',
            'name' => 'Test Free Wi-Fi Project',
            'program_id' => $program->id,
            'project_type_id' => $this->projectType->id,
            'lifecycle' => 'active',
        ]);
    }

    public function test_authenticated_user_can_view_sites_directory(): void
    {
        $response = $this->actingAs($this->projectManager)->get('/sites');
        $response->assertStatus(200);
    }

    public function test_encoder_cannot_manually_enroll_sites(): void
    {
        $response = $this->actingAs($this->encoder)->get('/sites/create');
        $response->assertStatus(403);
    }

    public function test_project_manager_can_enroll_site_with_new_location(): void
    {
        $payload = [
            'project_id' => $this->project->id,
            'use_existing_location' => false,
            'name' => 'Test Plaza Site',
            'category' => 'plaza',
            'psgc_barangay' => '021528001',
            'lat' => 17.6132,
            'lng' => 121.7270,
            'elevation_m' => 18.0,
            'current_status' => 'planned',
            'target_completion_date' => '2026-12-31',
        ];

        $response = $this->actingAs($this->projectManager)
            ->post('/sites', $payload);

        $response->assertRedirect('/sites');
        $this->assertDatabaseHas('locations', ['name' => 'Test Plaza Site', 'lat' => 17.613200]);
        $this->assertDatabaseHas('project_sites', [
            'project_id' => $this->project->id,
            'current_status' => 'planned',
        ]);
    }

    public function test_site_enrollment_can_link_existing_location_without_duplication(): void
    {
        $existingLoc = Location::create([
            'name' => 'Existing City Hall',
            'category' => 'lgu_hall',
            'psgc_barangay' => '021528001',
            'lat' => 17.6150,
            'lng' => 121.7250,
        ]);

        $payload = [
            'project_id' => $this->project->id,
            'use_existing_location' => true,
            'location_id' => $existingLoc->id,
            'current_status' => 'planned',
        ];

        $response = $this->actingAs($this->projectManager)
            ->post('/sites', $payload);

        $response->assertRedirect('/sites');
        $this->assertDatabaseHas('project_sites', [
            'project_id' => $this->project->id,
            'location_id' => $existingLoc->id,
        ]);
        // Location count should remain 1
        $this->assertEquals(1, Location::count());
    }

    public function test_institutions_directory_and_registration(): void
    {
        $res = $this->actingAs($this->projectManager)->get('/institutions');
        $res->assertStatus(200);

        $payload = [
            'kind' => 'deped_school',
            'name' => 'Tuguegarao Science High School',
            'external_id' => 'DEPED-300501',
            'psgc_code' => '021528000',
            'contact_person' => 'Dr. School Principal',
            'contact_email' => 'principal@deped.gov.ph',
        ];

        $postRes = $this->actingAs($this->projectManager)
            ->post('/institutions', $payload);

        $postRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('institutions', [
            'name' => 'Tuguegarao Science High School',
            'external_id' => 'DEPED-300501',
        ]);
    }
}
