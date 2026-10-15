<?php

namespace App\Http\Controllers\Reporting;

use App\Domain\Access\GrantResolver;
use App\Domain\Analytics\KpiCalculator;
use App\Domain\Audit\AuditLogger;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Location;
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

        $filters = $request->only([
            'project_id',
            'province_prefix',
            'district',
            'municipality',
            'barangay',
            'status',
            'start_date',
            'end_date',
        ]);

        $provinces = [
            ['name' => 'All Region 2', 'prefix' => ''],
            ['name' => 'Batanes', 'prefix' => '0209'],
            ['name' => 'Cagayan', 'prefix' => '0215'],
            ['name' => 'Isabela', 'prefix' => '0231'],
            ['name' => 'Nueva Vizcaya', 'prefix' => '0250'],
            ['name' => 'Quirino', 'prefix' => '0257'],
        ];

        $districts = [
            ['name' => 'Batanes Lone District', 'province' => 'Batanes', 'province_prefix' => '0209'],
            ['name' => 'Cagayan 1st District', 'province' => 'Cagayan', 'province_prefix' => '0215'],
            ['name' => 'Cagayan 2nd District', 'province' => 'Cagayan', 'province_prefix' => '0215'],
            ['name' => 'Cagayan 3rd District', 'province' => 'Cagayan', 'province_prefix' => '0215'],
            ['name' => 'Isabela 1st District', 'province' => 'Isabela', 'province_prefix' => '0231'],
            ['name' => 'Isabela 2nd District', 'province' => 'Isabela', 'province_prefix' => '0231'],
            ['name' => 'Isabela 3rd District', 'province' => 'Isabela', 'province_prefix' => '0231'],
            ['name' => 'Isabela 4th District', 'province' => 'Isabela', 'province_prefix' => '0231'],
            ['name' => 'Isabela 5th District', 'province' => 'Isabela', 'province_prefix' => '0231'],
            ['name' => 'Isabela 6th District', 'province' => 'Isabela', 'province_prefix' => '0231'],
            ['name' => 'Nueva Vizcaya Lone District', 'province' => 'Nueva Vizcaya', 'province_prefix' => '0250'],
            ['name' => 'Quirino Lone District', 'province' => 'Quirino', 'province_prefix' => '0257'],
        ];

        // Preload geographic hierarchy metadata for cascading filters
        $rawAddresses = Location::whereNotNull('address_details')->pluck('address_details');
        $munis = [];
        $bgys = [];
        $provPrefixMap = [
            'batanes' => '0209',
            'cagayan' => '0215',
            'isabela' => '0231',
            'nueva vizcaya' => '0250',
            'quirino' => '0257',
        ];

        foreach ($rawAddresses as $addr) {
            $parts = array_map('trim', explode(',', $addr));
            $bgy = $parts[0] ?? '';
            $mun = $parts[1] ?? '';
            $prov = $parts[2] ?? '';

            if ($mun && $prov) {
                $mKey = strtolower($prov . '|' . $mun);
                if (!isset($munis[$mKey])) {
                    $munis[$mKey] = [
                        'name' => $mun,
                        'province' => $prov,
                        'province_prefix' => $provPrefixMap[strtolower($prov)] ?? '',
                        'district' => Location::resolveDistrictName($mun, $prov),
                    ];
                }

                if ($bgy) {
                    $bKey = strtolower($mun . '|' . $bgy);
                    if (!isset($bgys[$bKey])) {
                        $bgys[$bKey] = [
                            'name' => $bgy,
                            'municipality' => $mun,
                            'province' => $prov,
                        ];
                    }
                }
            }
        }
        usort($munis, fn($a, $b) => strcmp($a['name'], $b['name']));
        usort($bgys, fn($a, $b) => strcmp($a['name'], $b['name']));

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

        // 1. Province Filter
        if (!empty($filters['province_prefix'])) {
            $prefix = $filters['province_prefix'];
            $allSites = $allSites->filter(function ($site) use ($prefix, $provPrefixMap) {
                $psgc = $site->resolvePsgcCode();
                if ($psgc && str_starts_with($psgc, $prefix)) return true;
                $provName = strtolower(trim($site->location?->hierarchy['province'] ?? ''));
                return ($provPrefixMap[$provName] ?? '') === $prefix;
            });
        }

        // 2. Congressional District Filter
        if (!empty($filters['district'])) {
            $district = $filters['district'];
            $allSites = $allSites->filter(function ($site) use ($district) {
                $hier = $site->location?->hierarchy;
                return ($hier['district'] ?? '') === $district;
            });
        }

        // 3. Municipality / City Filter
        if (!empty($filters['municipality'])) {
            $muni = strtolower(trim($filters['municipality']));
            $muniNorm = str_replace(['sta. ', 'sta ', 'sto. ', 'sto '], ['santa ', 'santa ', 'santo ', 'santo '], $muni);
            $allSites = $allSites->filter(function ($site) use ($muni, $muniNorm) {
                $hier = $site->location?->hierarchy;
                $siteMuni = strtolower(trim($hier['municipality'] ?? ''));
                $siteMuniNorm = str_replace(['sta. ', 'sta ', 'sto. ', 'sto '], ['santa ', 'santa ', 'santo ', 'santo '], $siteMuni);
                return $siteMuni === $muni
                    || $siteMuniNorm === $muniNorm
                    || str_contains($siteMuni, $muni)
                    || str_contains($siteMuniNorm, $muniNorm);
            });
        }

        // 4. Barangay Filter
        if (!empty($filters['barangay'])) {
            $bgy = strtolower(trim($filters['barangay']));
            $allSites = $allSites->filter(function ($site) use ($bgy) {
                $hier = $site->location?->hierarchy;
                $siteBgy = strtolower(trim($hier['barangay'] ?? ''));
                return $siteBgy === $bgy || str_contains($siteBgy, $bgy);
            });
        }

        $completedKeys = $this->kpiCalculator->getCompletedStatusKeys();
        $totalPreview = $allSites->count();
        $completedPreview = $allSites->filter(fn($s) => in_array($s->current_status, $completedKeys))->count();
        $operationalPreview = $allSites->filter(fn($s) => $s->current_status === 'operational')->count();
        $accomplishmentRate = $totalPreview > 0 ? round(($completedPreview / $totalPreview) * 100, 1) : 0.0;

        $previewRows = $allSites->take(50)->map(function ($site) {
            $loc = $site->location;
            $hierarchy = $loc ? $loc->hierarchy : [];
            return [
                'id' => $site->id,
                'site_code' => $site->site_code ?? 'N/A',
                'project_name' => $site->project?->name ?? 'Initiative',
                'facility_name' => $loc?->name ?? 'Public Wi-Fi Site',
                'province' => $hierarchy['province'] ?? 'Region 02',
                'district' => $hierarchy['district'] ?? 'N/A',
                'municipality' => $hierarchy['municipality'] ?? 'N/A',
                'barangay' => $hierarchy['barangay'] ?? 'N/A',
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
            'districts' => $districts,
            'municipalities' => array_values($munis),
            'barangays' => array_values($bgys),
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

        $filters = $request->only([
            'project_id',
            'province_prefix',
            'district',
            'municipality',
            'barangay',
            'status',
        ]);

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
