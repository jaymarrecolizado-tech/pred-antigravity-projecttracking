<?php

namespace App\Http\Controllers\Reporting;

use App\Domain\Access\GrantResolver;
use App\Domain\Analytics\KpiCalculator;
use App\Domain\Audit\AuditLogger;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\Export\ExcelReportGenerator;
use App\Domain\Reporting\ReportSubscription;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver,
        protected KpiCalculator $kpiCalculator,
        protected ExcelReportGenerator $excelGenerator,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'report.view') && !$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized to view reports.');
        }

        $filters = $request->only(['project_id', 'province_prefix', 'status', 'start_date', 'end_date']);

        $provinces = [
            ['name' => 'All Region 2', 'prefix' => ''],
            ['name' => 'Batanes', 'prefix' => '0209'],
            ['name' => 'Cagayan', 'prefix' => '0215'],
            ['name' => 'Isabela', 'prefix' => '0231'],
            ['name' => 'Nueva Vizcaya', 'prefix' => '0250'],
            ['name' => 'Quirino', 'prefix' => '0257'],
        ];

        $projects = Project::with('program')->get(['id', 'code', 'name', 'program_id']);
        $programs = Program::get(['id', 'name']);
        $statuses = StatusDef::distinct('key')->pluck('key');

        // Query filtered sites for preview matrix
        $query = ProjectSite::with(['project.program', 'location.barangay.parent.parent', 'institution'])
            ->latest('id');

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('current_status', $filters['status']);
        }

        $allSites = $query->get();

        if (!empty($filters['province_prefix'])) {
            $prefix = $filters['province_prefix'];
            $allSites = $allSites->filter(function ($site) use ($prefix) {
                $psgc = $site->resolvePsgcCode();
                return $psgc && str_starts_with($psgc, $prefix);
            });
        }

        $completedKeys = $this->kpiCalculator->getCompletedStatusKeys();
        $totalPreview = $allSites->count();
        $completedPreview = $allSites->filter(fn($s) => in_array($s->current_status, $completedKeys))->count();
        $operationalPreview = $allSites->filter(fn($s) => $s->current_status === 'operational')->count();
        $accomplishmentRate = $totalPreview > 0 ? round(($completedPreview / $totalPreview) * 100, 1) : 0.0;

        $previewRows = $allSites->take(25)->map(function ($site) {
            $loc = $site->location;
            $hierarchy = $loc ? $loc->hierarchy : [];
            return [
                'id' => $site->id,
                'site_code' => $site->site_code ?? 'N/A',
                'project_name' => $site->project?->name ?? 'Initiative',
                'province' => $hierarchy['province'] ?? 'Region 02',
                'municipality' => $hierarchy['municipality'] ?? 'N/A',
                'status' => $site->current_status,
                'target_date' => $site->target_completion_date ? Carbon::parse($site->target_completion_date)->toDateString() : 'N/A',
            ];
        });

        // Current User's Subscriptions
        $subscriptions = ReportSubscription::where('user_id', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Reports/Index', [
            'filters' => $filters,
            'provinces' => $provinces,
            'projects' => $projects,
            'programs' => $programs,
            'statuses' => $statuses,
            'matrix' => [
                'total_sites' => $totalPreview,
                'completed_sites' => $completedPreview,
                'operational_sites' => $operationalPreview,
                'accomplishment_rate' => $accomplishmentRate,
                'preview_rows' => $previewRows,
            ],
            'subscriptions' => $subscriptions,
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        return $this->exportFile($request, 'xlsx');
    }

    public function exportCsv(Request $request): BinaryFileResponse
    {
        return $this->exportFile($request, 'csv');
    }

    protected function exportFile(Request $request, string $format = 'xlsx'): BinaryFileResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'report.export') && !$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized to export reports.');
        }

        $filters = $request->only(['project_id', 'province_prefix', 'status']);

        $reportsDir = storage_path('app/exports');
        if (!File::isDirectory($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }

        $filename = 'DICT_R02_Free_WiFi_Report_' . Carbon::now()->format('Ymd_His') . '.' . $format;
        $filePath = $reportsDir . DIRECTORY_SEPARATOR . $filename;

        $this->excelGenerator->generateToFile($filePath, $filters);

        $action = $format === 'xlsx' ? 'report.export.excel' : 'report.export.csv';
        AuditLogger::log($action, null, null, [
            'filename' => $filename,
            'filters' => $filters,
        ], $user);

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    public function storeSubscription(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'format' => ['required', 'in:xlsx,pdf'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['required', 'email'],
            'filter_config' => ['nullable', 'array'],
        ]);

        $sub = ReportSubscription::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'frequency' => $validated['frequency'],
            'format' => $validated['format'],
            'recipients' => $validated['recipients'],
            'filter_config' => $validated['filter_config'] ?? [],
            'is_active' => true,
        ]);

        AuditLogger::log('report.subscription.create', $sub, null, $validated, $user);

        return back()->with('success', 'Report subscription created successfully.');
    }

    public function destroySubscription(ReportSubscription $subscription)
    {
        $user = auth()->user();
        if ($subscription->user_id !== $user->id && !$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        $subscription->delete();

        AuditLogger::log('report.subscription.delete', $subscription, null, null, $user);

        return back()->with('success', 'Subscription cancelled.');
    }
}
