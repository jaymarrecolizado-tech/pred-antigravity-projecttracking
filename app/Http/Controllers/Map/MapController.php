<?php

namespace App\Http\Controllers\Map;

use App\Domain\Access\GrantResolver;
use App\Domain\Geo\PsgcArea;
use App\Domain\Portfolio\Project;
use App\Domain\Registry\ProjectSite;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'map.view') && !$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized to access interactive map.');
        }

        // 1. Projects for Filter Slide-over Tray
        $projects = Project::with(['program', 'projectType.statusDefs'])
            ->where('lifecycle', 'active')
            ->orderBy('name')
            ->get();

        // 2. Fetch Enrolled Sites with Coordinates
        $sitesQuery = ProjectSite::query()
            ->with([
                'project.program',
                'project.projectType.statusDefs',
                'location.barangay.parent.parent',
                'institution.primaryLocation',
            ])
            ->where(function ($q) {
                $q->whereHas('location', fn ($lq) => $lq->whereNotNull('lat')->whereNotNull('lng'))
                  ->orWhereHas('institution.primaryLocation', fn ($iq) => $iq->whereNotNull('lat')->whereNotNull('lng'));
            });

        $allSites = $sitesQuery->get();

        // Build GeoJSON FeatureCollection
        $features = [];
        $completedCount = 0;
        $operationalCount = 0;

        foreach ($allSites as $site) {
            $coords = $site->getCoordinates();
            if (!$coords) {
                continue;
            }

            $project = $site->project;
            $type = $project?->projectType;
            $statusDefs = $type?->statusDefs ?? collect();
            $statusDef = $statusDefs->firstWhere('key', $site->current_status);

            $isCompleted = (bool) ($statusDef?->is_completed ?? false);
            $isOperational = in_array($site->current_status, ['operational', 'live_production', 'fully_adopted', 'graduated_certified'], true);

            if ($isCompleted) {
                $completedCount++;
            }
            if ($isOperational) {
                $operationalCount++;
            }

            $bgy = $site->location?->barangay;
            $mun = $bgy?->parent;
            $prov = $mun?->parent;

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$coords['lng'], $coords['lat']],
                ],
                'properties' => [
                    'id' => $site->id,
                    'site_code' => $site->site_code ?: ('SITE-' . $site->id),
                    'name' => $site->location?->name ?: ($site->institution?->name ?: 'Site'),
                    'category' => $site->location?->category ?: 'other',
                    'project_id' => $project?->id,
                    'project_code' => $project?->code,
                    'project_name' => $project?->name,
                    'project_color' => $project?->color ?: ($type?->color_code ?: '#2563eb'),
                    'program_name' => $project?->program?->name,
                    'kind' => $type?->kind ?: 'site',
                    'marker_shape' => $type?->marker_shape ?: 'circle',
                    'current_status' => $site->current_status,
                    'is_completed' => $isCompleted,
                    'is_operational' => $isOperational,
                    'elevation_m' => $site->location?->elevation_m,
                    'target_completion_date' => $site->target_completion_date?->format('Y-m-d'),
                    'barangay' => $bgy?->name,
                    'municipality' => $mun?->name,
                    'province' => $prov?->name,
                    'psgc_code' => $site->location?->psgc_barangay ?: ($site->institution?->psgc_code),
                ],
            ];
        }

        $totalCount = count($features);
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0.0;
        $operationalRate = $totalCount > 0 ? round(($operationalCount / $totalCount) * 100, 1) : 0.0;

        $provinces = PsgcArea::where('level', 'prov')
            ->orderBy('name')
            ->get(['code', 'name', 'centroid_lat', 'centroid_lng']);

        return Inertia::render('Map/Index', [
            'initialGeojson' => [
                'type' => 'FeatureCollection',
                'features' => $features,
            ],
            'projects' => $projects,
            'provinces' => $provinces,
            'summary' => [
                'total_sites' => $totalCount,
                'completed_count' => $completedCount,
                'operational_count' => $operationalCount,
                'completion_rate' => $completionRate,
                'operational_rate' => $operationalRate,
            ],
            'initialFocus' => [
                'lat' => $request->input('lat') ? (float) $request->input('lat') : null,
                'lng' => $request->input('lng') ? (float) $request->input('lng') : null,
                'zoom' => $request->input('zoom') ? (int) $request->input('zoom') : null,
                'project_id' => $request->input('project_id') ? (int) $request->input('project_id') : null,
            ],
        ]);
    }
}
