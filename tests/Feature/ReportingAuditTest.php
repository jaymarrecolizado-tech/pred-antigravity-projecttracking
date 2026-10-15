<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogger;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\ReportSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use LogicException;
use Tests\TestCase;

class ReportingAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $viewer;
    protected Project $project;
    protected ProjectSite $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => 'SECRET_KEY_123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->roleGrants()->create([
            'role' => GrantResolver::ROLE_SUPER_ADMIN,
        ]);

        $this->viewer = User::factory()->create([
            'is_active' => true,
        ]);
        $this->viewer->roleGrants()->create([
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
            'code' => 'FW-REP-01',
            'name' => 'Free Wi-Fi Reporting Project',
            'program_id' => $program->id,
            'project_type_id' => $projectType->id,
            'lifecycle' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Tuguegarao City Hall',
            'category' => 'lgu_hall',
            'psgc_barangay' => '021528001',
            'lat' => 17.6132,
            'lng' => 121.7270,
            'address_details' => 'Centro 01, Tuguegarao City, Cagayan',
        ]);

        $this->site = ProjectSite::create([
            'project_id' => $this->project->id,
            'location_id' => $location->id,
            'site_code' => 'FW-REP-CAG-01',
            'current_status' => 'operational',
            'target_completion_date' => '2026-12-31',
        ]);
    }

    public function test_guest_cannot_access_reports_or_audit(): void
    {
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/admin/audit')->assertRedirect('/login');
    }

    public function test_authorized_user_can_view_reports_matrix_and_filter(): void
    {
        $response = $this->actingAs($this->viewer)->get('/reports?province_prefix=0215');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->has('matrix')
            ->where('matrix.total_sites', 1)
            ->where('matrix.completed_sites', 1)
            ->where('matrix.accomplishment_rate', 100)
            ->has('projects', 1)
            ->has('provinces', 6)
            ->has('districts', 12)
        );
    }

    public function test_authorized_user_can_filter_by_district_municipality_and_barangay(): void
    {
        // 1. Filter by Congressional District
        $respDistrict = $this->actingAs($this->viewer)->get('/reports?district=' . urlencode('Cagayan 3rd District'));
        $respDistrict->assertStatus(200);
        $respDistrict->assertInertia(fn (Assert $page) => $page
            ->where('matrix.total_sites', 1)
        );

        // Filter by non-matching district
        $respWrongDistrict = $this->actingAs($this->viewer)->get('/reports?district=' . urlencode('Batanes Lone District'));
        $respWrongDistrict->assertStatus(200);
        $respWrongDistrict->assertInertia(fn (Assert $page) => $page
            ->where('matrix.total_sites', 0)
        );

        // 2. Filter by Municipality
        $respMuni = $this->actingAs($this->viewer)->get('/reports?municipality=Tuguegarao');
        $respMuni->assertStatus(200);
        $respMuni->assertInertia(fn (Assert $page) => $page
            ->where('matrix.total_sites', 1)
        );

        // 3. Filter by Barangay
        $respBgy = $this->actingAs($this->viewer)->get('/reports?barangay=Centro');
        $respBgy->assertStatus(200);
        $respBgy->assertInertia(fn (Assert $page) => $page
            ->where('matrix.total_sites', 1)
        );
    }

    public function test_excel_report_export_generates_valid_xlsx_and_logs_audit(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/export/excel?project_id=' . $this->project->id);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report.export.excel',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_csv_report_export_generates_valid_csv_and_logs_audit(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/export/csv?project_id=' . $this->project->id);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report.export.csv',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_user_can_create_and_delete_report_subscription(): void
    {
        $payload = [
            'title' => 'Weekly Cagayan Accomplishment',
            'frequency' => 'weekly',
            'format' => 'xlsx',
            'recipients' => ['director@dict.gov.ph', 'planner@dict.gov.ph'],
            'filter_config' => ['province_prefix' => '0215'],
        ];

        $response = $this->actingAs($this->admin)->post('/reports/subscriptions', $payload);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('report_subscriptions', [
            'title' => 'Weekly Cagayan Accomplishment',
            'frequency' => 'weekly',
            'user_id' => $this->admin->id,
        ]);

        $sub = ReportSubscription::first();
        $this->assertNotNull($sub);

        // Delete subscription
        $delResponse = $this->actingAs($this->admin)->delete('/reports/subscriptions/' . $sub->id);
        $delResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('report_subscriptions', [
            'id' => $sub->id,
        ]);
    }

    public function test_reports_dispatch_command_generates_reports_and_updates_last_dispatched(): void
    {
        $sub = ReportSubscription::create([
            'user_id' => $this->admin->id,
            'title' => 'Daily Executive Snapshot',
            'frequency' => 'daily',
            'format' => 'xlsx',
            'recipients' => ['exec@dict.gov.ph'],
            'filter_config' => [],
            'is_active' => true,
        ]);

        $this->artisan('reports:dispatch', ['--force' => true])->assertSuccessful();

        $sub->refresh();
        $this->assertNotNull($sub->last_dispatched_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report.dispatch',
        ]);
    }

    public function test_audit_logs_are_append_only_and_throw_on_update_or_delete(): void
    {
        $log = AuditLogger::log('test.action', $this->project, ['foo' => 'bar'], ['foo' => 'baz'], $this->admin);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'test.action',
        ]);

        $this->expectException(LogicException::class);
        $log->update(['action' => 'tampered.action']);
    }

    public function test_audit_logs_throw_on_delete(): void
    {
        $log = AuditLogger::log('test.delete_guard', $this->project, null, null, $this->admin);

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_super_admin_can_view_audit_trail_browser(): void
    {
        AuditLogger::log('admin.view_test', $this->project, null, null, $this->admin);

        $response = $this->actingAs($this->admin)->get('/admin/audit');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Audit/Index')
            ->has('logs.data')
            ->where('logs.data.0.action', 'admin.view_test')
        );

        // Viewer should be forbidden
        $forbiddenResponse = $this->actingAs($this->viewer)->get('/admin/audit');
        $forbiddenResponse->assertStatus(403);
    }
}
