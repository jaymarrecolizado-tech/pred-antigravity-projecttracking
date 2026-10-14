<?php

namespace Tests\Unit;

use App\Domain\Geo\BoundaryValidator;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Portfolio\StatusWorkflow;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\Attachment;
use App\Domain\Reporting\FieldReport;
use App\Domain\Reporting\FraudDetector;
use App\Domain\Reporting\Intervention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FraudDetectorTest extends TestCase
{
    use RefreshDatabase;

    protected FraudDetector $detector;
    protected ProjectSite $site;
    protected ProjectType $projectType;

    protected function setUp(): void
    {
        parent::setUp();

        $boundary = new BoundaryValidator();
        $workflow = new StatusWorkflow();
        $this->detector = new FraudDetector($boundary, $workflow);

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
            'next_keys' => ['survey_conducted'],
        ]);

        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'survey_conducted',
            'label' => 'Survey Conducted',
            'sort_order' => 2,
            'is_completed' => false,
            'next_keys' => ['installed'],
        ]);

        $program = Program::create(['name' => 'Free Wi-Fi', 'color' => '#2563eb']);

        $project = Project::create([
            'code' => 'FW-TEST',
            'name' => 'Test Project',
            'program_id' => $program->id,
            'project_type_id' => $this->projectType->id,
            'lifecycle' => 'active',
        ]);

        // Location in Tuguegarao
        $location = Location::create([
            'name' => 'Tuguegarao Plaza',
            'category' => 'plaza',
            'psgc_barangay' => '021528001',
            'lat' => 17.613200,
            'lng' => 121.727000,
        ]);

        $this->site = ProjectSite::create([
            'project_id' => $project->id,
            'location_id' => $location->id,
            'site_code' => 'TEST-001',
            'current_status' => 'planned',
        ]);
    }

    public function test_gps_within_500m_generates_no_flags(): void
    {
        // 50m away from site coordinates
        $flags = $this->detector->evaluate(
            $this->site,
            'survey_conducted',
            17.613400,
            121.727100
        );

        $this->assertEmpty($flags);
    }

    public function test_gps_outside_500m_triggers_outside_proximity_flag(): void
    {
        // ~20 km away
        $flags = $this->detector->evaluate(
            $this->site,
            'survey_conducted',
            17.800000,
            121.727000
        );

        $this->assertContains(FraudDetector::FLAG_OUTSIDE_PROXIMITY, $flags);
    }

    public function test_illegal_status_transition_triggers_flag(): void
    {
        // From 'planned', allowed next is only 'survey_conducted', NOT 'installed'
        $flags = $this->detector->evaluate(
            $this->site,
            'installed',
            17.613200,
            121.727000
        );

        $this->assertContains(FraudDetector::FLAG_ILLEGAL_TRANSITION, $flags);
    }

    public function test_duplicate_image_sha256_triggers_flag(): void
    {
        $admin = User::factory()->create();
        $inv = Intervention::create([
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
            'created_by' => $admin->id,
        ]);
        $rep = FieldReport::create(['intervention_id' => $inv->id]);
        Attachment::create([
            'field_report_id' => $rep->id,
            'file_path' => '/storage/test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'sha256_hash' => 'dummy_existing_hash_123',
        ]);

        $flags = $this->detector->evaluate(
            $this->site,
            'survey_conducted',
            17.613200,
            121.727000,
            ['dummy_existing_hash_123']
        );

        $this->assertContains(FraudDetector::FLAG_DUPLICATE_IMAGE, $flags);
    }
}
