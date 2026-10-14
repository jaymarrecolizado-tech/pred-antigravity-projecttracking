<?php

namespace Database\Seeders;

use App\Domain\Analytics\KpiCalculator;
use App\Domain\Geo\PsgcArea;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Registry\Institution;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\FieldReport;
use App\Domain\Reporting\Intervention;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTelemetrySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@dict.gov.ph')->first() ?? User::first();
        if (!$admin) {
            $this->command->error('Run UserSeeder first.');
            return;
        }

        $this->command->info('Seeding 350+ realistic Region 2 multi-province infrastructure & program sites...');

        // Fetch programs and types
        $programs = Program::all()->keyBy('name');
        $types = ProjectType::with('statusDefs')->get()->keyBy('kind');

        $typeSite = ProjectType::where('kind', 'site')->first();
        $typeInst = ProjectType::where('kind', 'institution')->first();
        $typeBeneficiary = ProjectType::whereIn('kind', ['activity', 'beneficiary'])->first() ?? $typeSite;
        $typeStudy = ProjectType::whereIn('kind', ['milestone', 'study'])->first() ?? $typeSite;

        // 1. Ensure core projects exist for the 4 archetypes
        $projectFW = Project::firstOrCreate(
            ['code' => 'FW4A-R02-2026'],
            [
                'name' => 'Free Wi-Fi for All - Regional Rollout 2026',
                'program_id' => $programs['Free Wi-Fi for All - Public Places']?->id ?? Program::first()->id,
                'project_type_id' => $typeSite->id,
                'lifecycle' => 'active',
                'budget' => 45000000.00,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ]
        );

        $projectGovNet = Project::firstOrCreate(
            ['code' => 'GVN-R02-CORR'],
            [
                'name' => 'GovNet Regional Backbone Corridors',
                'program_id' => $programs['Government Network (GovNet)']?->id ?? Program::first()->id,
                'project_type_id' => $typeSite->id,
                'lifecycle' => 'active',
                'budget' => 28000000.00,
                'start_date' => '2026-01-15',
                'end_date' => '2026-11-30',
            ]
        );

        $projectStarlink = Project::firstOrCreate(
            ['code' => 'LEO-GIDA-R02'],
            [
                'name' => 'LEO Satellite High-Speed GIDA Connectivity',
                'program_id' => $programs['LEO Satellite / Starlink Connectivity for GIDA']?->id ?? Program::first()->id,
                'project_type_id' => $typeSite->id,
                'lifecycle' => 'active',
                'budget' => 18500000.00,
                'start_date' => '2026-02-01',
                'end_date' => '2026-12-15',
            ]
        );

        $projectELGU = Project::firstOrCreate(
            ['code' => 'ELGU-R02-ADOPT'],
            [
                'name' => 'eLGU Local Government Digital System Onboarding',
                'program_id' => $programs['e-LGU (Local Government Digital System)']?->id ?? Program::first()->id,
                'project_type_id' => $typeInst->id,
                'lifecycle' => 'active',
                'budget' => 12000000.00,
                'start_date' => '2026-01-01',
                'end_date' => '2026-10-31',
            ]
        );

        $projectSUC = Project::firstOrCreate(
            ['code' => 'SUC-NET-MOD'],
            [
                'name' => 'SUC Campus Network Modernization',
                'program_id' => $programs['SUC Campus Network Modernization']?->id ?? Program::first()->id,
                'project_type_id' => $typeInst->id,
                'lifecycle' => 'active',
                'budget' => 15000000.00,
                'start_date' => '2026-01-10',
                'end_date' => '2026-12-31',
            ]
        );

        $projectDJPH = Project::firstOrCreate(
            ['code' => 'DJPH-2026-SCHOLARS'],
            [
                'name' => 'DigitalJobsPH Technical Freelance Training Cohorts',
                'program_id' => $programs['DigitalJobsPH (DJPH) Freelancing Training']?->id ?? Program::first()->id,
                'project_type_id' => $typeBeneficiary->id,
                'lifecycle' => 'active',
                'budget' => 9500000.00,
                'start_date' => '2026-02-01',
                'end_date' => '2026-11-30',
            ]
        );

        $projectNICTHS = Project::firstOrCreate(
            ['code' => 'NICTHS-R02-SWEEP'],
            [
                'name' => 'National ICT Household Survey Field Sweeps',
                'program_id' => $programs['National ICT Household Survey (NICTHS)']?->id ?? Program::first()->id,
                'project_type_id' => $typeStudy->id,
                'lifecycle' => 'active',
                'budget' => 6000000.00,
                'start_date' => '2026-03-01',
                'end_date' => '2026-09-30',
            ]
        );

        // Province configurations with realistic bounds
        $provinceSpecs = [
            '0209' => [
                'name' => 'Batanes',
                'prov_code' => '0209000000',
                'target_sites' => 24,
                'center' => [20.4485, 121.9708],
                'radius' => 0.25,
            ],
            '0215' => [
                'name' => 'Cagayan',
                'prov_code' => '0215000000',
                'target_sites' => 125,
                'center' => [17.6132, 121.7270],
                'radius' => 0.65,
            ],
            '0231' => [
                'name' => 'Isabela',
                'prov_code' => '0231000000',
                'target_sites' => 135,
                'center' => [17.1485, 121.8885],
                'radius' => 0.70,
            ],
            '0250' => [
                'name' => 'Nueva Vizcaya',
                'prov_code' => '0250000000',
                'target_sites' => 52,
                'center' => [16.4833, 121.1500],
                'radius' => 0.40,
            ],
            '0257' => [
                'name' => 'Quirino',
                'prov_code' => '0257000000',
                'target_sites' => 38,
                'center' => [16.3167, 121.5000],
                'radius' => 0.35,
            ],
        ];

        $categories = ['plaza', 'lgu_hall', 'rhu', 'school', 'public_market', 'terminal'];
        $isps = ['PLDT Enterprise', 'Converge ICT', 'Starlink High Performance', 'DITO Telecommunity', 'Globe Business'];

        $totalSeeded = 0;
        $now = Carbon::now();

        foreach ($provinceSpecs as $prefix => $spec) {
            // Get LGUs in this province
            $lgus = PsgcArea::where('province_code', $spec['prov_code'])
                ->whereIn('level', ['city', 'mun'])
                ->get();

            if ($lgus->isEmpty()) {
                // Fallback LGU codes if not in DB
                $lgus = collect([
                    (object) ['code' => $prefix . '010000', 'name' => $spec['name'] . ' Central'],
                ]);
            }

            $countForProv = $spec['target_sites'];

            for ($i = 1; $i <= $countForProv; $i++) {
                $lgu = $lgus->random();
                $cat = $categories[array_rand($categories)];

                // Coordinates with jitter from center
                $latJitter = (mt_rand(-1000, 1000) / 1000.0) * $spec['radius'];
                $lngJitter = (mt_rand(-1000, 1000) / 1000.0) * $spec['radius'];

                $lat = round($spec['center'][0] + $latJitter, 6);
                $lng = round($spec['center'][1] + $lngJitter, 6);

                $barangayPsgc = substr($lgu->code, 0, 6) . sprintf('%03d', rand(1, 40)) . '0';

                // Status distribution
                $roll = rand(1, 100);
                $isOperational = $roll <= 72;
                $isInProgress = $roll > 72 && $roll <= 88;
                $isEarly = $roll > 88 && $roll <= 94;
                $isBacklog = $roll > 94; // 6% backlog

                if ($isOperational) {
                    $status = 'operational';
                    $targetDate = $now->copy()->addMonths(rand(1, 6))->toDateString();
                } elseif ($isInProgress) {
                    $status = rand(0, 1) ? 'installed' : 'equipment_delivered';
                    $targetDate = $now->copy()->addDays(rand(10, 45))->toDateString();
                } elseif ($isEarly) {
                    $status = rand(0, 1) ? 'survey_conducted' : 'planned';
                    $targetDate = $now->copy()->addMonths(rand(2, 5))->toDateString();
                } else {
                    // Backlog: target date is in the past!
                    $daysOverdue = rand(15, 115);
                    $targetDate = $now->copy()->subDays($daysOverdue)->toDateString();
                    $status = rand(0, 1) ? 'installed' : 'survey_conducted';
                }

                // Archetype distribution
                $archetypeRoll = rand(1, 100);

                if ($archetypeRoll <= 60) {
                    // Kind A: Free Wi-Fi / GovNet / Starlink
                    $proj = ($prefix === '0209' || rand(1, 10) <= 3) ? $projectStarlink : ($cat === 'lgu_hall' && rand(1, 5) === 1 ? $projectGovNet : $projectFW);
                    $locationName = "{$lgu->name} " . ucwords(str_replace('_', ' ', $cat));

                    $loc = Location::create([
                        'name' => $locationName,
                        'category' => $cat,
                        'psgc_barangay' => $barangayPsgc,
                        'lat' => $lat,
                        'lng' => $lng,
                        'elevation_m' => rand(10, 350),
                        'is_verified' => true,
                    ]);

                    $siteCode = "FW4A-{$prefix}-" . sprintf('%04d', $i);
                    $site = ProjectSite::create([
                        'project_id' => $proj->id,
                        'location_id' => $loc->id,
                        'site_code' => $siteCode,
                        'current_status' => $status,
                        'target_completion_date' => $targetDate,
                        'enrolled_at' => $now->copy()->subDays(rand(30, 200)),
                    ]);

                    // Add historical milestones for operational sites
                    if ($status === 'operational') {
                        $inv1 = Intervention::create([
                            'project_site_id' => $site->id,
                            'intervention_type' => 'hardware_installation',
                            'previous_status' => 'planned',
                            'new_status' => 'operational',
                            'occurred_at' => $now->copy()->subDays(rand(5, 60)),
                            'created_by' => $admin->id,
                            'source' => 'web_form',
                        ]);

                        FieldReport::create([
                            'intervention_id' => $inv1->id,
                            'notes' => "Commissioned and active. Signal benchmark verified across {$locationName}.",
                            'submission_lat' => $lat,
                            'submission_lng' => $lng,
                            'gps_accuracy_m' => rand(2, 6),
                            'device_timestamp' => $inv1->occurred_at,
                            'server_timestamp' => $inv1->occurred_at,
                            'custom_values' => [
                                'isp_provider' => $isps[array_rand($isps)],
                                'bandwidth_mbps' => rand(50, 300),
                                'measured_download_mbps' => rand(45, 280),
                            ],
                            'verification_status' => 'verified',
                            'verified_by' => $admin->id,
                            'verified_at' => $now->copy()->subDays(2),
                        ]);
                    }
                } elseif ($archetypeRoll <= 80) {
                    // Kind B: eLGU or SUC
                    $isSuc = rand(1, 4) === 1;
                    $proj = $isSuc ? $projectSUC : $projectELGU;
                    $instName = $isSuc ? "{$lgu->name} University Campus" : "{$lgu->name} Municipal Government";

                    $inst = Institution::create([
                        'kind' => $isSuc ? 'suc' : 'lgu',
                        'name' => $instName,
                        'external_id' => "LGU-{$prefix}-" . sprintf('%03d', $i),
                        'psgc_code' => $lgu->code,
                        'contact_person' => 'Hon. Municipal Mayor',
                    ]);

                    $loc = Location::create([
                        'name' => $instName . " Administrative Building",
                        'category' => 'lgu_hall',
                        'psgc_barangay' => $barangayPsgc,
                        'lat' => $lat,
                        'lng' => $lng,
                        'is_verified' => true,
                    ]);
                    $inst->primary_location_id = $loc->id;
                    $inst->save();

                    $site = ProjectSite::create([
                        'project_id' => $proj->id,
                        'institution_id' => $inst->id,
                        'location_id' => $loc->id,
                        'site_code' => "ADOPT-{$prefix}-" . sprintf('%03d', $i),
                        'current_status' => $status === 'operational' ? 'operational' : ($status === 'installed' ? 'operational' : 'planned'),
                        'target_completion_date' => $targetDate,
                        'enrolled_at' => $now->copy()->subDays(rand(40, 180)),
                    ]);
                } elseif ($archetypeRoll <= 93) {
                    // Kind C: DigitalJobsPH Training Cohort (GAD disaggregated)
                    $femaleCount = rand(18, 38);
                    $maleCount = rand(10, 24);

                    $loc = Location::create([
                        'name' => "{$lgu->name} Community Training Hub",
                        'category' => 'school',
                        'psgc_barangay' => $barangayPsgc,
                        'lat' => $lat,
                        'lng' => $lng,
                        'is_verified' => true,
                    ]);

                    $site = ProjectSite::create([
                        'project_id' => $projectDJPH->id,
                        'location_id' => $loc->id,
                        'site_code' => "DJPH-{$prefix}-" . sprintf('%03d', $i),
                        'current_status' => $status === 'operational' ? 'operational' : 'planned',
                        'target_completion_date' => $targetDate,
                        'enrolled_at' => $now->copy()->subDays(rand(20, 120)),
                    ]);

                    $inv = Intervention::create([
                        'project_site_id' => $site->id,
                        'intervention_type' => 'training_completion',
                        'previous_status' => 'planned',
                        'new_status' => $site->current_status,
                        'occurred_at' => $now->copy()->subDays(rand(5, 45)),
                        'created_by' => $admin->id,
                        'source' => 'web_form',
                    ]);

                    FieldReport::create([
                        'intervention_id' => $inv->id,
                        'notes' => "Cohort concluded. 100% attendance rate for GAD beneficiaries in {$lgu->name}.",
                        'submission_lat' => $lat,
                        'submission_lng' => $lng,
                        'custom_values' => [
                            'female_count' => $femaleCount,
                            'male_count' => $maleCount,
                            'graduates' => $femaleCount + $maleCount,
                        ],
                        'verification_status' => 'verified',
                        'verified_by' => $admin->id,
                        'verified_at' => $now->copy()->subDays(1),
                    ]);
                } else {
                    // Kind D: NICTHS Study Milestone
                    $loc = Location::create([
                        'name' => "{$lgu->name} Survey Sample Enumeration Area",
                        'category' => 'other',
                        'psgc_barangay' => $barangayPsgc,
                        'lat' => $lat,
                        'lng' => $lng,
                        'is_verified' => true,
                    ]);

                    ProjectSite::create([
                        'project_id' => $projectNICTHS->id,
                        'location_id' => $loc->id,
                        'site_code' => "NICTHS-{$prefix}-" . sprintf('%03d', $i),
                        'current_status' => $status === 'operational' ? 'operational' : 'planned',
                        'target_completion_date' => $targetDate,
                        'enrolled_at' => $now->copy()->subDays(rand(15, 90)),
                    ]);
                }

                $totalSeeded++;
            }

            $this->command->info("  ✓ Seeded {$countForProv} sites across {$spec['name']} ({$prefix})");
        }

        $this->command->info("Seeding complete! Total sites seeded: {$totalSeeded}.");

        // 3. Rebuild Rollup Tables for instantaneous analytics
        $this->command->info('Rebuilding daily rollup aggregations across all Region 2 hierarchy levels...');
        $upserted = app(KpiCalculator::class)->rebuildRollups();
        $this->command->info("Rollup rebuild finished: {$upserted} aggregate records compiled.");
    }
}
