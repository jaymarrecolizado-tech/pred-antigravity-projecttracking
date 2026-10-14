<?php

namespace Tests\Feature;

use App\Domain\Analytics\RollupDaily;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeWifiImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $typeSite = ProjectType::create([
            'name' => 'Site Deployment',
            'kind' => 'site',
            'marker_shape' => 'circle',
            'color_code' => '#2563eb',
        ]);

        StatusDef::create([
            'project_type_id' => $typeSite->id,
            'key' => 'operational',
            'label' => 'Operational',
            'sort_order' => 6,
            'is_completed' => true,
        ]);

        StatusDef::create([
            'project_type_id' => $typeSite->id,
            'key' => 'planned',
            'label' => 'Planned',
            'sort_order' => 1,
            'is_completed' => false,
        ]);

        StatusDef::create([
            'project_type_id' => $typeSite->id,
            'key' => 'installed',
            'label' => 'Installed',
            'sort_order' => 4,
            'is_completed' => false,
        ]);

        Program::create([
            'name' => 'Free Wi-Fi for All - Public Places',
            'color' => '#2563eb',
        ]);
    }

    public function test_wifi_import_command_executes_successfully(): void
    {
        $this->artisan('wifi:import', ['--limit' => 10])->assertSuccessful();

        // Verify sites imported
        $this->assertGreaterThan(0, ProjectSite::count());
        $this->assertGreaterThan(0, Location::count());

        $firstSite = ProjectSite::with('location')->first();
        $this->assertNotNull($firstSite);
        $this->assertNotEmpty($firstSite->site_code);
        $this->assertNotNull($firstSite->location);
        $this->assertNotNull($firstSite->location->lat);
        $this->assertNotNull($firstSite->location->lng);

        // Verify custom attributes
        $this->assertIsArray($firstSite->custom_attributes);
        $this->assertArrayHasKey('link_provider', $firstSite->custom_attributes);
        $this->assertArrayHasKey('bandwidth_mbps', $firstSite->custom_attributes);

        // Verify rollup records compiled
        $this->assertGreaterThan(0, RollupDaily::count());
    }
}
