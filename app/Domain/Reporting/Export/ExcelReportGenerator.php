<?php

namespace App\Domain\Reporting\Export;

use App\Domain\Analytics\KpiCalculator;
use App\Domain\Portfolio\Project;
use App\Domain\Registry\ProjectSite;
use Carbon\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExcelReportGenerator
{
    public function __construct(
        protected KpiCalculator $kpiCalculator,
    ) {}

    /**
     * Generate an Excel or CSV workbook for the given filters and write to target path.
     */
    public function generateToFile(string $targetFilePath, array $filters = []): string
    {
        $isCsv = str_ends_with(strtolower($targetFilePath), '.csv');
        $writer = $isCsv ? new \OpenSpout\Writer\CSV\Writer() : new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($targetFilePath);

        // Header Rows
        $writer->addRow(Row::fromValues(['DEPARTMENT OF INFORMATION AND COMMUNICATIONS TECHNOLOGY - REGION 02']));
        $writer->addRow(Row::fromValues(['FREE WI-FI FOR ALL PROJECT • REGIONAL SITE STATUS & ACCOMPLISHMENT MATRIX']));
        $writer->addRow(Row::fromValues(['Generated At:', Carbon::now()->toDateTimeString(), 'System: RECON Platform (DICT R02)']));
        $writer->addRow(Row::fromValues([])); // Empty row

        // Query Sites
        $query = ProjectSite::with(['project.program', 'project.projectType', 'location.barangay.parent.parent', 'institution'])
            ->latest('id');

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('current_status', $filters['status']);
        }

        $sites = $query->get();

        // Geographic and status filters
        if (!empty($filters['province_prefix'])) {
            $prefix = $filters['province_prefix'];
            $sites = $sites->filter(function ($site) use ($prefix) {
                $psgc = $site->resolvePsgcCode();
                if ($psgc && str_starts_with($psgc, $prefix)) return true;
                $provName = $site->location?->hierarchy['province'] ?? '';
                $pPrefix = match (strtolower(trim($provName))) {
                    'batanes' => '0209',
                    'cagayan' => '0215',
                    'isabela' => '0231',
                    'nueva vizcaya' => '0250',
                    'quirino' => '0257',
                    default => '',
                };
                return $pPrefix === $prefix;
            });
        }

        if (!empty($filters['district'])) {
            $district = $filters['district'];
            $sites = $sites->filter(function ($site) use ($district) {
                $hier = $site->location?->hierarchy;
                return ($hier['district'] ?? '') === $district;
            });
        }

        if (!empty($filters['municipality'])) {
            $muni = strtolower(trim($filters['municipality']));
            $muniNorm = str_replace(['sta. ', 'sta ', 'sto. ', 'sto '], ['santa ', 'santa ', 'santo ', 'santo '], $muni);
            $sites = $sites->filter(function ($site) use ($muni, $muniNorm) {
                $hier = $site->location?->hierarchy;
                $siteMuni = strtolower(trim($hier['municipality'] ?? ''));
                $siteMuniNorm = str_replace(['sta. ', 'sta ', 'sto. ', 'sto '], ['santa ', 'santa ', 'santo ', 'santo '], $siteMuni);
                return $siteMuni === $muni
                    || $siteMuniNorm === $muniNorm
                    || str_contains($siteMuni, $muni)
                    || str_contains($siteMuniNorm, $muniNorm);
            });
        }

        if (!empty($filters['barangay'])) {
            $bgy = strtolower(trim($filters['barangay']));
            $sites = $sites->filter(function ($site) use ($bgy) {
                $hier = $site->location?->hierarchy;
                $siteBgy = strtolower(trim($hier['barangay'] ?? ''));
                return $siteBgy === $bgy || str_contains($siteBgy, $bgy);
            });
        }

        // Summary KPI Section
        $totalSites = $sites->count();
        $completedKeys = $this->kpiCalculator->getCompletedStatusKeys();
        $completedSites = $sites->filter(fn($s) => in_array($s->current_status, $completedKeys))->count();
        $operationalSites = $sites->filter(fn($s) => $s->current_status === 'operational')->count();
        $completionRate = $totalSites > 0 ? round(($completedSites / $totalSites) * 100, 1) : 0.0;

        $writer->addRow(Row::fromValues(['--- EXECUTIVE KPI SUMMARY ---']));
        $writer->addRow(Row::fromValues(['Total Enrolled Sites:', $totalSites]));
        $writer->addRow(Row::fromValues(['Completed Sites:', $completedSites]));
        $writer->addRow(Row::fromValues(['Operational Sites:', $operationalSites]));
        $writer->addRow(Row::fromValues(['Physical Accomplishment Rate:', "{$completionRate}%"]));
        $writer->addRow(Row::fromValues([])); // Empty row

        // Column Headers
        $headers = [
            'Site Code',
            'Delivery Lot / Contract',
            'Location Name',
            'Facility Type',
            'Province',
            'Congressional District',
            'Municipality / City',
            'Barangay',
            'Current Status',
            'Target Completion Date',
            'Latitude',
            'Longitude',
            'Enrolled At',
        ];
        $writer->addRow(Row::fromValues($headers));

        // Data Rows
        foreach ($sites as $site) {
            $loc = $site->location;
            $hierarchy = $loc ? $loc->hierarchy : [];
            $coords = $site->getCoordinates();

            $writer->addRow(Row::fromValues([
                $site->site_code ?? 'N/A',
                $site->project?->name ?? 'Free Wi-Fi for All',
                $loc?->name ?? 'Public Wi-Fi Site',
                strtoupper($loc?->category ?? 'OTHER'),
                $hierarchy['province'] ?? 'Region 02',
                $hierarchy['district'] ?? 'N/A',
                $hierarchy['municipality'] ?? 'N/A',
                $hierarchy['barangay'] ?? 'N/A',
                strtoupper(str_replace('_', ' ', $site->current_status)),
                $site->target_completion_date ? Carbon::parse($site->target_completion_date)->toDateString() : 'N/A',
                $coords['lat'] ?? '',
                $coords['lng'] ?? '',
                $site->enrolled_at ? Carbon::parse($site->enrolled_at)->toDateString() : '',
            ]));
        }

        $writer->close();

        return $targetFilePath;
    }
}
