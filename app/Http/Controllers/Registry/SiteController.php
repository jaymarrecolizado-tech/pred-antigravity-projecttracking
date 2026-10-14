<?php

namespace App\Http\Controllers\Registry;

use App\Domain\Access\GrantResolver;
use App\Domain\Geo\PsgcArea;
use App\Domain\Portfolio\Project;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Domain\Registry\SiteImportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver,
        protected SiteImportService $importService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'project.view')) {
            abort(403, 'Unauthorized.');
        }

        $query = ProjectSite::query()
            ->with([
                'project.program',
                'project.projectType.statusDefs',
                'location.barangay.parent.parent',
                'institution',
                'assignedEncoder',
            ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('site_code', 'like', "%{$search}%")
                  ->orWhereHas('location', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('institution', fn ($iq) => $iq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($status = $request->input('status')) {
            $query->where('current_status', $status);
        }

        if ($provinceCode = $request->input('province_code')) {
            $query->whereHas('location', function ($lq) use ($provinceCode) {
                $lq->where('psgc_barangay', 'like', substr($provinceCode, 0, 4) . '%');
            });
        }

        $sites = $query->latest('id')->paginate(25)->withQueryString();

        $projects = Project::orderBy('name')->get(['id', 'name', 'code']);
        $provinces = PsgcArea::where('level', 'prov')->orderBy('name')->get(['code', 'name']);

        // Aggregate statistics
        $stats = [
            'total_sites' => ProjectSite::count(),
            'operational_count' => ProjectSite::whereIn('current_status', ['operational', 'live_production', 'fully_adopted', 'graduated_certified'])->count(),
            'in_progress_count' => ProjectSite::whereNotIn('current_status', ['operational', 'live_production', 'fully_adopted', 'graduated_certified', 'decommissioned', 'cancelled', 'dropped'])->count(),
            'locations_count' => Location::count(),
        ];

        return Inertia::render('Sites/Index', [
            'sites' => $sites,
            'projects' => $projects,
            'provinces' => $provinces,
            'stats' => $stats,
            'filters' => $request->only(['search', 'project_id', 'status', 'province_code']),
            'canEnroll' => $this->grantResolver->allows($user, 'site.enroll'),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'site.enroll')) {
            abort(403, 'Unauthorized to enroll sites.');
        }

        $projects = Project::with(['projectType.statusDefs', 'projectType.fieldDefs'])
            ->where('lifecycle', 'active')
            ->orderBy('name')
            ->get();

        $existingLocations = Location::orderBy('name')->limit(100)->get();
        $provinces = PsgcArea::where('level', 'prov')->orderBy('name')->get(['code', 'name']);
        $municipalities = PsgcArea::whereIn('level', ['city', 'mun'])->orderBy('name')->get(['code', 'name', 'parent_code']);

        return Inertia::render('Sites/Create', [
            'projects' => $projects,
            'existingLocations' => $existingLocations,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'site.enroll')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'use_existing_location' => ['boolean'],
            'location_id' => ['nullable', 'required_if:use_existing_location,true', 'exists:locations,id'],
            'name' => ['nullable', 'required_if:use_existing_location,false', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:32'],
            'psgc_barangay' => ['nullable', 'required_if:use_existing_location,false', 'string', 'min:9', 'max:10'],
            'lat' => ['nullable', 'required_if:use_existing_location,false', 'numeric', 'between:15.5,21.5'],
            'lng' => ['nullable', 'required_if:use_existing_location,false', 'numeric', 'between:120.5,123.0'],
            'elevation_m' => ['nullable', 'numeric'],
            'current_status' => ['nullable', 'string', 'max:64'],
            'target_completion_date' => ['nullable', 'date'],
            'custom_attributes' => ['nullable', 'array'],
        ]);

        $project = Project::with('projectType.statusDefs')->findOrFail($validated['project_id']);

        if (!empty($validated['use_existing_location'])) {
            $locationId = $validated['location_id'];
        } else {
            $newLoc = Location::create([
                'name' => $validated['name'],
                'category' => $validated['category'] ?? 'other',
                'psgc_barangay' => $validated['psgc_barangay'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'elevation_m' => $validated['elevation_m'] ?? null,
                'is_verified' => true,
            ]);
            $locationId = $newLoc->id;
        }

        $initialStatus = $validated['current_status'] ?: ($project->projectType?->initialStatus()?->key ?? 'planned');
        $siteCode = ($project->code ? $project->code . '-S' : 'SITE-') . str_pad((string) (ProjectSite::where('project_id', $project->id)->count() + 1), 3, '0', STR_PAD_LEFT);

        ProjectSite::create([
            'project_id' => $project->id,
            'location_id' => $locationId,
            'site_code' => $siteCode,
            'current_status' => $initialStatus,
            'target_completion_date' => $validated['target_completion_date'] ?? null,
            'custom_attributes' => $validated['custom_attributes'] ?? null,
        ]);

        return redirect()->route('sites.index')->with('success', "Site '{$siteCode}' successfully enrolled in {$project->name}.");
    }

    public function import(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'site.import')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.name' => ['required', 'string'],
            'rows.*.psgc_barangay' => ['required', 'string'],
            'rows.*.lat' => ['required', 'numeric'],
            'rows.*.lng' => ['required', 'numeric'],
            'rows.*.category' => ['nullable', 'string'],
            'rows.*.target_completion_date' => ['nullable', 'date'],
        ]);

        $project = Project::with('projectType.statusDefs')->findOrFail($validated['project_id']);
        $result = $this->importService->import($project, $validated['rows']);

        $msg = "Import completed: {$result['imported']} sites enrolled ({$result['reused_locations']} linked to existing locations).";
        if ($result['skipped'] > 0) {
            $msg .= " {$result['skipped']} rows skipped due to validation.";
        }

        return back()->with('success', $msg);
    }

    public function show(Request $request, ProjectSite $site): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'project.view')) {
            abort(403, 'Unauthorized.');
        }

        $site->load([
            'project.program',
            'project.projectType.statusDefs',
            'project.projectType.fieldDefs',
            'project.projectType.interventionTypes',
            'location.barangay.parent.parent',
            'institution',
            'assignedEncoder',
        ]);

        $interventions = \App\Domain\Reporting\Intervention::query()
            ->where('project_site_id', $site->id)
            ->with([
                'author',
                'fieldReport.attachments',
                'fieldReport.verifier',
            ])
            ->orderBy('occurred_at', 'desc')
            ->get();

        return Inertia::render('Sites/Show', [
            'site' => $site,
            'interventions' => $interventions,
            'canSubmit' => $this->grantResolver->allows($user, 'submission.create', $site->project_id),
            'canVerify' => $this->grantResolver->allows($user, 'submission.verify') || $this->grantResolver->allows($user, '*'),
        ]);
    }
}

