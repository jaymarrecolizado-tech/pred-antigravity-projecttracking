<?php

namespace App\Domain\Registry;

use App\Domain\Geo\BoundaryValidator;
use App\Domain\Geo\PsgcArea;
use App\Domain\Portfolio\Project;
use Illuminate\Support\Facades\DB;

class SiteImportService
{
    public function __construct(
        protected BoundaryValidator $boundaryValidator
    ) {}

    /**
     * Batch import rows into project sites.
     *
     * @param Project $project
     * @param array<array{name: string, category?: string, psgc_barangay: string, lat: float, lng: float, elevation_m?: float|null, custom_attributes?: array|null, target_completion_date?: string|null}> $rows
     * @return array{imported: int, reused_locations: int, skipped: int, errors: array<string>}
     */
    public function import(Project $project, array $rows): array
    {
        $imported = 0;
        $reusedLocations = 0;
        $skipped = 0;
        $errors = [];

        $projectType = $project->projectType;
        $initialStatus = $projectType?->initialStatus()?->key ?? 'planned';

        // Pre-load all locations for proximity check
        $existingLocations = Location::select(['id', 'lat', 'lng', 'name'])->get();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;
            $name = trim($row['name'] ?? '');
            $psgc = trim($row['psgc_barangay'] ?? '');
            $lat = isset($row['lat']) ? (float) $row['lat'] : null;
            $lng = isset($row['lng']) ? (float) $row['lng'] : null;

            if (!$name || !$psgc || $lat === null || $lng === null) {
                $errors[] = "Row #{$rowNum}: Name, PSGC code, and lat/lng coordinates are mandatory.";
                $skipped++;
                continue;
            }

            // Validate PSGC existence
            $area = PsgcArea::where('code', $psgc)->first();
            if (!$area) {
                $errors[] = "Row #{$rowNum}: PSGC code '{$psgc}' does not exist in Region 2 geographic database.";
                $skipped++;
                continue;
            }

            // Check Region 2 boundary
            if (!$this->boundaryValidator->isWithinRegion2($lat, $lng)) {
                $errors[] = "Row #{$rowNum}: Coordinates ({$lat}, {$lng}) fall outside Region 2 geographic boundaries.";
                $skipped++;
                continue;
            }

            // Deduplication / Asset Reuse check (< 50m)
            $matchedLocation = null;
            foreach ($existingLocations as $loc) {
                if ($this->boundaryValidator->isNearby($lat, $lng, (float) $loc->lat, (float) $loc->lng, 50.0)) {
                    $matchedLocation = $loc;
                    break;
                }
            }

            DB::transaction(function () use (
                $project,
                $matchedLocation,
                $name,
                $psgc,
                $lat,
                $lng,
                $row,
                $initialStatus,
                &$imported,
                &$reusedLocations,
                &$existingLocations
            ) {
                if ($matchedLocation) {
                    $locationId = $matchedLocation->id;
                    $reusedLocations++;
                } else {
                    $newLoc = Location::create([
                        'name' => $name,
                        'category' => $row['category'] ?? 'other',
                        'psgc_barangay' => $psgc,
                        'lat' => $lat,
                        'lng' => $lng,
                        'elevation_m' => $row['elevation_m'] ?? null,
                        'is_verified' => true,
                        'address_details' => $row['address_details'] ?? null,
                    ]);
                    $locationId = $newLoc->id;
                    $existingLocations->push($newLoc);
                }

                $siteCode = ($project->code ? $project->code . '-S' : 'SITE-') . str_pad((string) (ProjectSite::where('project_id', $project->id)->count() + 1), 3, '0', STR_PAD_LEFT);

                ProjectSite::create([
                    'project_id' => $project->id,
                    'location_id' => $locationId,
                    'site_code' => $siteCode,
                    'current_status' => $initialStatus,
                    'target_completion_date' => $row['target_completion_date'] ?? null,
                    'custom_attributes' => $row['custom_attributes'] ?? null,
                ]);

                $imported++;
            });
        }

        return [
            'imported' => $imported,
            'reused_locations' => $reusedLocations,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
