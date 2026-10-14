<?php

namespace App\Http\Controllers\Portfolio;

use App\Domain\Access\GrantResolver;
use App\Domain\Geo\PsgcArea;
use App\Domain\Portfolio\IndicatorTarget;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectScope;
use App\Domain\Portfolio\ProjectType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'project.view')) {
            abort(403, 'Unauthorized to view project portfolio.');
        }

        $query = Project::query()
            ->with(['program', 'projectType.statusDefs', 'scopes.area', 'indicatorTargets'])
            ->withCount('scopes');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($programId = $request->input('program_id')) {
            $query->where('program_id', $programId);
        }

        if ($kind = $request->input('kind')) {
            $query->whereHas('projectType', function ($q) use ($kind) {
                $q->where('kind', $kind);
            });
        }

        if ($lifecycle = $request->input('lifecycle')) {
            $query->where('lifecycle', $lifecycle);
        }

        $projects = $query->orderBy('name')->get();

        // Summary metrics
        $metrics = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('lifecycle', 'active')->count(),
            'total_budget' => (float) Project::sum('budget'),
            'kind_counts' => [
                'site' => Project::whereHas('projectType', fn ($q) => $q->where('kind', 'site'))->count(),
                'institution' => Project::whereHas('projectType', fn ($q) => $q->where('kind', 'institution'))->count(),
                'activity' => Project::whereHas('projectType', fn ($q) => $q->where('kind', 'activity'))->count(),
                'milestone' => Project::whereHas('projectType', fn ($q) => $q->where('kind', 'milestone'))->count(),
            ],
        ];

        $programs = Program::orderBy('name')->get(['id', 'name', 'color']);
        $projectTypes = ProjectType::orderBy('name')->get(['id', 'name', 'kind', 'color_code']);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'metrics' => $metrics,
            'programs' => $programs,
            'projectTypes' => $projectTypes,
            'filters' => $request->only(['search', 'program_id', 'kind', 'lifecycle']),
            'canCreate' => $this->grantResolver->allows($user, 'project.create'),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'project.create')) {
            abort(403, 'Unauthorized to create projects.');
        }

        $programs = Program::orderBy('name')->get(['id', 'name', 'color']);
        $projectTypes = ProjectType::with(['statusDefs', 'fieldDefs'])->orderBy('name')->get();

        // Provinces and cities/municipalities in Region 2 for the scope selector
        $provinces = PsgcArea::where('level', 'prov')
            ->orderBy('name')
            ->get(['code', 'name', 'level']);

        $municipalities = PsgcArea::whereIn('level', ['city', 'mun'])
            ->orderBy('name')
            ->get(['code', 'name', 'parent_code', 'level']);

        return Inertia::render('Projects/Create', [
            'programs' => $programs,
            'projectTypes' => $projectTypes,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'project.create')) {
            abort(403, 'Unauthorized to create projects.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64', 'unique:projects,code'],
            'name' => ['required', 'string', 'max:255'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'project_type_id' => ['required', 'exists:project_types,id'],
            'lifecycle' => ['required', 'in:draft,active,closed,archived'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'funding_source' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'color' => ['nullable', 'string', 'max:9'],
            'scopes' => ['nullable', 'array'],
            'scopes.*' => ['string', 'size:10'],
            'targets' => ['nullable', 'array'],
            'targets.*.indicator_key' => ['required_with:targets', 'string', 'max:64'],
            'targets.*.psgc_code' => ['nullable', 'string', 'size:10'],
            'targets.*.period' => ['required_with:targets', 'string', 'max:16'],
            'targets.*.target_value' => ['required_with:targets', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $project = Project::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'program_id' => $validated['program_id'] ?? null,
                'project_type_id' => $validated['project_type_id'],
                'lifecycle' => $validated['lifecycle'],
                'budget' => $validated['budget'] ?? null,
                'funding_source' => $validated['funding_source'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'color' => $validated['color'] ?? null,
            ]);

            // Save scopes
            if (!empty($validated['scopes'])) {
                foreach (array_unique($validated['scopes']) as $psgcCode) {
                    ProjectScope::create([
                        'project_id' => $project->id,
                        'psgc_code' => $psgcCode,
                    ]);
                }
            }

            // Save indicator targets
            if (!empty($validated['targets'])) {
                foreach ($validated['targets'] as $target) {
                    if (!empty($target['indicator_key']) && !empty($target['period'])) {
                        IndicatorTarget::create([
                            'project_id' => $project->id,
                            'indicator_key' => $target['indicator_key'],
                            'psgc_code' => !empty($target['psgc_code']) ? $target['psgc_code'] : null,
                            'period' => $target['period'],
                            'target_value' => $target['target_value'] ?? 0,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('projects.index')->with('success', "Project '{$validated['name']}' created successfully.");
    }
}
