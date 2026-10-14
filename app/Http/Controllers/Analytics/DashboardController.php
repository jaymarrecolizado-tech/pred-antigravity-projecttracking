<?php

namespace App\Http\Controllers\Analytics;

use App\Domain\Analytics\KpiCalculator;
use App\Domain\Analytics\RollupDaily;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\Intervention;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected KpiCalculator $kpiCalculator,
    ) {}

    public function index(Request $request): Response
    {
        $today = Carbon::now()->toDateString();

        // 1. Core Portfolio Summary
        $totalProjects = Project::count();
        $totalSites = ProjectSite::count();
        $completionRate = $this->kpiCalculator->calculateCompletionRate();
        $scopeCoverage = $this->kpiCalculator->calculateScopeCoverage();
        $cycleTime = $this->kpiCalculator->calculateAverageCycleTime();
        $opMetrics = $this->kpiCalculator->calculateOperationalMetrics();

        // 2. Backlog & Aging Analysis
        $backlogMetrics = $this->kpiCalculator->calculateBacklogAndAging();

        // 3. Provincial Leaderboard
        $provincialLeaderboard = $this->kpiCalculator->calculateProvincialLeaderboard();

        // 4. GAD Sex-Disaggregated Indicators
        $gadMetrics = $this->kpiCalculator->calculateGadMetrics();

        // 5. Burn-Up Trajectory
        $burnupTrajectory = $this->kpiCalculator->calculateBurnupTrend();

        // 6. Archetype Breakdown
        $archetypeStats = [
            'kind_a' => [
                'name' => 'Site Deployments',
                'projects_count' => Project::whereHas('projectType', fn($q) => $q->where('kind', 'site'))->count(),
                'sites_count' => ProjectSite::whereHas('project.projectType', fn($q) => $q->where('kind', 'site'))->count(),
            ],
            'kind_b' => [
                'name' => 'Institution Adoption',
                'projects_count' => Project::whereHas('projectType', fn($q) => $q->where('kind', 'institution'))->count(),
                'sites_count' => ProjectSite::whereHas('project.projectType', fn($q) => $q->where('kind', 'institution'))->count(),
            ],
            'kind_c' => [
                'name' => 'Beneficiaries & Capacity',
                'projects_count' => Project::whereHas('projectType', fn($q) => $q->where('kind', 'beneficiary'))->count(),
                'sites_count' => ProjectSite::whereHas('project.projectType', fn($q) => $q->where('kind', 'beneficiary'))->count(),
            ],
            'kind_d' => [
                'name' => 'Studies & Frameworks',
                'projects_count' => Project::whereHas('projectType', fn($q) => $q->where('kind', 'study'))->count(),
                'sites_count' => ProjectSite::whereHas('project.projectType', fn($q) => $q->where('kind', 'study'))->count(),
            ],
        ];

        // 7. Priority Projects with Calculated Accomplishment
        $projects = Project::with(['program', 'projectType', 'sites'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($project) {
                $sitesCount = $project->sites->count();
                $rate = $this->kpiCalculator->calculateCompletionRate($project->id);
                return [
                    'id' => $project->id,
                    'code' => $project->code,
                    'name' => $project->name,
                    'program' => $project->program?->name ?? 'Regional Initiative',
                    'kind' => strtoupper($project->projectType?->kind ?? 'A'),
                    'kind_name' => $project->projectType?->name ?? 'Standard',
                    'sites_count' => $sitesCount,
                    'percent' => (int) round($rate),
                    'lifecycle' => $project->lifecycle ?? 'active',
                ];
            });

        // 8. Recent Verified / Field Interventions
        $recentInterventions = Intervention::with(['projectSite.project', 'projectSite.location', 'author', 'fieldReport'])
            ->latest('occurred_at')
            ->take(6)
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'site_code' => $inv->projectSite?->site_code ?? 'SITE-UNKNOWN',
                    'project_name' => $inv->projectSite?->project?->name ?? 'Project',
                    'location_name' => $inv->projectSite?->location?->name ?? 'Location',
                    'intervention_type' => ucwords(str_replace('_', ' ', $inv->intervention_type)),
                    'previous_status' => $inv->previous_status,
                    'new_status' => $inv->new_status,
                    'author' => $inv->author?->name ?? 'Field Officer',
                    'occurred_at' => $inv->occurred_at ? Carbon::parse($inv->occurred_at)->diffForHumans() : 'Recent',
                    'verification_status' => $inv->fieldReport?->verification_status ?? 'pending',
                ];
            });

        return Inertia::render('Dashboard', [
            'summary' => [
                'total_projects' => $totalProjects,
                'total_sites' => $totalSites,
                'completion_rate' => $completionRate,
                'operational_sites' => $opMetrics['operational_sites'],
                'offline_sites' => $opMetrics['offline_sites'],
                'operational_rate' => $opMetrics['operational_rate'],
                'average_cycle_time' => $cycleTime,
                'scope_coverage' => $scopeCoverage,
            ],
            'backlog' => $backlogMetrics,
            'provincialLeaderboard' => $provincialLeaderboard,
            'gad' => $gadMetrics,
            'trajectory' => $burnupTrajectory,
            'archetypeStats' => $archetypeStats,
            'priorityProjects' => $projects,
            'recentInterventions' => $recentInterventions,
        ]);
    }
}
