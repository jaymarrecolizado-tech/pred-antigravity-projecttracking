<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\FieldReport;
use App\Domain\Reporting\Intervention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class FieldSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $encoder;
    protected User $reviewer;
    protected ProjectSite $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->encoder = User::factory()->create([
            'is_active' => true,
        ]);
        $this->encoder->roleGrants()->create([
            'role' => GrantResolver::ROLE_ENCODER,
        ]);

        $this->reviewer = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => 'SECRET999',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->reviewer->roleGrants()->create([
            'role' => GrantResolver::ROLE_REVIEWER,
        ]);

        $projectType = ProjectType::create([
            'name' => 'Site Deployment',
            'kind' => 'site',
            'marker_shape' => 'circle',
            'color_code' => '#2563eb',
        ]);

        StatusDef::create([
            'project_type_id' => $projectType->id,
            'key' => 'planned',
            'label' => 'Planned',
            'sort_order' => 1,
            'is_completed' => false,
            'next_keys' => ['survey_conducted'],
        ]);

        StatusDef::create([
            'project_type_id' => $projectType->id,
            'key' => 'survey_conducted',
            'label' => 'Survey Conducted',
            'sort_order' => 2,
            'is_completed' => false,
            'next_keys' => ['installed'],
        ]);

        $program = Program::create(['name' => 'Free Wi-Fi', 'color' => '#2563eb']);

        $project = Project::create([
            'code' => 'FW-SUB-TEST',
            'name' => 'Free Wi-Fi Submission Test',
            'program_id' => $program->id,
            'project_type_id' => $projectType->id,
            'lifecycle' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Basco Plaza',
            'category' => 'plaza',
            'psgc_barangay' => '020901001',
            'lat' => 20.448500,
            'lng' => 121.970800,
        ]);

        $this->site = ProjectSite::create([
            'project_id' => $project->id,
            'location_id' => $location->id,
            'site_code' => 'BAT-001',
            'current_status' => 'planned',
        ]);
    }

    public function test_encoder_can_submit_field_report(): void
    {
        $payload = [
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
            'occurred_at' => now()->toDateTimeString(),
            'notes' => 'Survey executed at Basco Plaza. Line of sight confirmed.',
            'submission_lat' => 20.448550,
            'submission_lng' => 121.970820,
            'gps_accuracy_m' => 4.2,
            'custom_values' => [
                'isp_provider' => 'Starlink',
            ],
        ];

        $response = $this->actingAs($this->encoder)
            ->post('/submissions', $payload);

        $response->assertRedirect('/sites/' . $this->site->id);

        $this->assertDatabaseHas('interventions', [
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
        ]);

        $this->assertDatabaseHas('field_reports', [
            'notes' => 'Survey executed at Basco Plaza. Line of sight confirmed.',
            'verification_status' => 'pending',
        ]);

        // Verify site's current status was updated
        $this->assertEquals('survey_conducted', $this->site->fresh()->current_status);
    }

    public function test_interventions_are_append_only_and_throw_on_update_or_delete(): void
    {
        $intervention = Intervention::create([
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
            'created_by' => $this->encoder->id,
        ]);

        // Attempt update
        $this->expectException(LogicException::class);
        $intervention->update(['new_status' => 'tampered_status']);
    }

    public function test_interventions_throw_on_delete(): void
    {
        $intervention = Intervention::create([
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
            'created_by' => $this->encoder->id,
        ]);

        // Attempt delete
        $this->expectException(LogicException::class);
        $intervention->delete();
    }

    public function test_reviewer_can_view_queue_and_verify_report(): void
    {
        $intervention = Intervention::create([
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
            'created_by' => $this->encoder->id,
        ]);

        $report = FieldReport::create([
            'intervention_id' => $intervention->id,
            'notes' => 'Awaiting sign off.',
            'verification_status' => 'pending',
        ]);

        // 1. Visit Review Queue
        $res = $this->actingAs($this->reviewer)->get('/reviews');
        $res->assertStatus(200);

        // 2. Verify Report
        $verifyRes = $this->actingAs($this->reviewer)
            ->post("/reviews/{$report->id}/verify", [
                'status' => 'verified',
                'review_notes' => 'Documentation complete and approved.',
            ]);

        $verifyRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('field_reports', [
            'id' => $report->id,
            'verification_status' => 'verified',
            'review_notes' => 'Documentation complete and approved.',
        ]);
    }

    public function test_site_dossier_loads_chronological_interventions(): void
    {
        Intervention::create([
            'project_site_id' => $this->site->id,
            'intervention_type' => 'site_survey',
            'new_status' => 'survey_conducted',
            'created_by' => $this->encoder->id,
        ]);

        $response = $this->actingAs($this->reviewer)->get('/sites/' . $this->site->id);
        $response->assertStatus(200);
    }
}
