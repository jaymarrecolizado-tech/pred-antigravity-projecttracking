<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InteractiveMapTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ProjectSite $site;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->user->roleGrants()->create([
            'role' => GrantResolver::ROLE_EXECUTIVE_VIEWER,
        ]);

        $projectType = ProjectType::create([
            'name' => 'Site Deployment',
            'kind' => 'site',
            'marker_shape' => 'circle',
            'color_code' => '#2563eb',
        ]);

        StatusDef::create([
            'project_type_id' => $projectType->id,
            'key' => 'operational',
            'label' => 'Operational',
            'sort_order' => 6,
            'is_completed' => true,
        ]);

        $program = Program::create([
            'name' => 'Free Wi-Fi for All',
            'color' => '#2563eb',
        ]);

        $this->project = Project::create([
            'code' => 'FW-MAP-01',
            'name' => 'Free Wi-Fi Map Initiative',
            'program_id' => $program->id,
            'project_type_id' => $projectType->id,
            'lifecycle' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Tuguegarao City Plaza',
            'category' => 'plaza',
            'psgc_barangay' => '021528001',
            'lat' => 17.613200,
            'lng' => 121.727000,
        ]);

        $this->site = ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $location->id,
            'site_code' => 'FW4A-CAG-MAP',
            'current_status' => 'operational',
        ]);
    }

    public function test_guest_is_redirected_from_map(): void
    {
        $response = $this->get('/map');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_map_with_geojson_features(): void
    {
        $response = $this->actingAs($this->user)->get('/map');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Map/Index')
            ->has('initialGeojson.features', 1)
            ->where('initialGeojson.features.0.properties.site_code', 'FW4A-CAG-MAP')
            ->where('initialGeojson.features.0.properties.is_operational', true)
            ->where('initialGeojson.features.0.properties.is_completed', true)
            ->where('initialGeojson.features.0.geometry.coordinates', [121.727000, 17.613200])
            ->has('summary')
            ->where('summary.total_sites', 1)
            ->where('summary.operational_rate', 100)
            ->has('projects', 1)
        );
    }

    public function test_map_preserves_initial_focus_coordinates_and_project_filter(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/map?lat=17.6132&lng=121.7270&zoom=14&project_id=' . $this->project->id);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Map/Index')
            ->where('initialFocus.lat', 17.6132)
            ->where('initialFocus.lng', 121.727)
            ->where('initialFocus.zoom', 14)
            ->where('initialFocus.project_id', $this->project->id)
        );
    }
}
