<?php

namespace Tests\Feature;

use App\Console\Commands\RebuildRollupCommand;
use App\Domain\Access\GrantResolver;
use App\Domain\Analytics\KpiCalculator;
use App\Domain\Analytics\RollupDaily;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\FieldReport;
use App\Domain\Reporting\Intervention;
use App\Jobs\UpdateRollupJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsRollupTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ProjectType $projectType;
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

        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'installed',
            'label' => 'Installed',
            'sort_order' => 4,
            'is_completed' => false,
        ]);

        StatusDef::create([
            'project_type_id' => $this->projectType->id,
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
            'code' => 'FW-ANALYTICS-01',
            'name' => 'Free Wi-Fi Rollout Project',
            'program_id' => $program->id,
            'project_type_id' => $this->projectType->id,
            'lifecycle' => 'active',
        ]);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard_with_precomputed_analytics(): void
    {
        $loc = Location::create([
            'name' => 'Tuguegarao Public Market',
            'category' => 'public_market',
            'psgc_barangay' => '021528001',
            'lat' => 17.6132,
            'lng' => 121.7270,
        ]);

        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc->id,
            'site_code' => 'FW-TEST-001',
            'current_status' => 'operational',
            'enrolled_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->user)->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('summary')
            ->where('summary.total_projects', 1)
            ->where('summary.total_sites', 1)
            ->where('summary.operational_sites', 1)
            ->where('summary.completion_rate', 100)
            ->has('backlog')
            ->has('provincialLeaderboard', 5)
            ->has('gad')
            ->has('trajectory')
            ->has('archetypeStats')
            ->has('priorityProjects', 1)
        );
    }

    public function test_kpi_calculator_calculates_correct_completion_rate_and_scope_coverage(): void
    {
        $calculator = app(KpiCalculator::class);

        $loc1 = Location::create([
            'name' => 'Basco Plaza',
            'category' => 'plaza',
            'psgc_barangay' => '020901001', // Batanes
            'lat' => 20.4485,
            'lng' => 121.9708,
        ]);

        $loc2 = Location::create([
            'name' => 'Ilagan Hall',
            'category' => 'lgu_hall',
            'psgc_barangay' => '023114001', // Isabela
            'lat' => 17.1485,
            'lng' => 121.8885,
        ]);

        // 1 Operational, 1 Installed (in-progress)
        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc1->id,
            'site_code' => 'FW-BAT-01',
            'current_status' => 'operational',
            'enrolled_at' => now()->subDays(20),
        ]);

        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc2->id,
            'site_code' => 'FW-ISA-02',
            'current_status' => 'installed',
            'enrolled_at' => now()->subDays(10),
        ]);

        $completionRate = $calculator->calculateCompletionRate($this->project->id);
        $this->assertEquals(50.0, $completionRate); // 1 out of 2 completed

        $scope = $calculator->calculateScopeCoverage($this->project->id);
        $this->assertEquals(1, $scope['covered_lgus']);
        $this->assertGreaterThan(0, $scope['total_lgus']);
    }

    public function test_kpi_calculator_calculates_backlog_aging_buckets(): void
    {
        $calculator = app(KpiCalculator::class);
        $now = Carbon::parse('2026-10-14 00:00:00');

        $loc = Location::create([
            'name' => 'Test Site Location',
            'category' => 'other',
            'psgc_barangay' => '021528001',
            'lat' => 17.61,
            'lng' => 121.72,
        ]);

        // Overdue by 10 days (<30d)
        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc->id,
            'site_code' => 'BACKLOG-10D',
            'current_status' => 'planned',
            'target_completion_date' => '2026-10-04',
        ]);

        // Overdue by 45 days (30-60d)
        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc->id,
            'site_code' => 'BACKLOG-45D',
            'current_status' => 'installed',
            'target_completion_date' => '2026-08-30',
        ]);

        // Overdue by 105 days (>90d)
        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc->id,
            'site_code' => 'BACKLOG-105D',
            'current_status' => 'planned',
            'target_completion_date' => '2026-07-01',
        ]);

        // Completed site past target completion date (should NOT be counted in backlog)
        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc->id,
            'site_code' => 'COMPLETED-NOT-BACKLOG',
            'current_status' => 'operational',
            'target_completion_date' => '2026-08-01',
        ]);

        $backlog = $calculator->calculateBacklogAndAging($this->project->id, $now);

        $this->assertEquals(3, $backlog['total_backlog']);
        $this->assertEquals(1, $backlog['under_30']);
        $this->assertEquals(1, $backlog['tier_30_60']);
        $this->assertEquals(0, $backlog['tier_60_90']);
        $this->assertEquals(1, $backlog['over_90']);
    }

    public function test_rebuild_rollup_command_and_job_populate_rollup_daily(): void
    {
        $loc = Location::create([
            'name' => 'Tuguegarao Capitol',
            'category' => 'lgu_hall',
            'psgc_barangay' => '021528001',
            'lat' => 17.6132,
            'lng' => 121.7270,
        ]);

        ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $loc->id,
            'site_code' => 'FW-ROLLUP-01',
            'current_status' => 'operational',
            'enrolled_at' => now()->subDays(5),
        ]);

        // Run artisan command
        $this->artisan('rollup:rebuild', [
            '--project' => $this->project->id,
            '--date' => now()->toDateString(),
        ])->assertSuccessful();

        // Verify rollup records created
        $this->assertDatabaseHas('rollup_daily', [
            'project_id' => $this->project->id,
            'total_sites' => 1,
            'completed_sites' => 1,
            'operational_sites' => 1,
        ]);

        // Verify async job execution
        $job = new UpdateRollupJob($this->project->id, now()->toDateString());
        $job->handle(app(KpiCalculator::class));

        $this->assertGreaterThan(0, RollupDaily::where('project_id', $this->project->id)->count());
    }

    public function test_gad_metrics_disaggregates_female_and_male_counts(): void
    {
        $calculator = app(KpiCalculator::class);
        $gad = $calculator->calculateGadMetrics();

        $this->assertArrayHasKey('total_beneficiaries', $gad);
        $this->assertArrayHasKey('female_count', $gad);
        $this->assertArrayHasKey('male_count', $gad);
        $this->assertArrayHasKey('female_percentage', $gad);
        $this->assertArrayHasKey('male_percentage', $gad);
        $this->assertGreaterThan(0, $gad['total_beneficiaries']);
        $this->assertEquals(100.0, round($gad['female_percentage'] + $gad['male_percentage'], 0));
    }
}
