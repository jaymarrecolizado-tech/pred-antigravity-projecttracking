<?php

namespace Database\Seeders;

use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\Attachment;
use App\Domain\Reporting\FieldReport;
use App\Domain\Reporting\Intervention;
use App\Models\User;
use Illuminate\Database\Seeder;

class InterventionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@dict.gov.ph')->first() ?? User::first();
        if (!$admin) {
            return;
        }

        // 1. Tuguegarao City Hall Plaza Site
        $s1 = ProjectSite::where('site_code', 'FW4A-CAG-001')->first();
        if ($s1) {
            // Milestone 1: Site Survey
            $i1 = Intervention::create([
                'project_site_id' => $s1->id,
                'intervention_type' => 'site_survey',
                'previous_status' => 'planned',
                'new_status' => 'survey_conducted',
                'occurred_at' => now()->subDays(60),
                'created_by' => $admin->id,
                'source' => 'web_form',
            ]);
            FieldReport::create([
                'intervention_id' => $i1->id,
                'notes' => 'Feasibility survey passed. Clear line-of-sight to plaza grounds and civic center. Structural mast suitable for mounting.',
                'submission_lat' => 17.613200,
                'submission_lng' => 121.727000,
                'gps_accuracy_m' => 4.2,
                'device_timestamp' => now()->subDays(60),
                'server_timestamp' => now()->subDays(60),
                'custom_values' => [
                    'isp_provider' => 'PLDT Enterprise',
                    'bandwidth_mbps' => 100,
                    'ap_count' => 4,
                ],
                'verification_status' => 'verified',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(59),
                'review_notes' => 'Verified site survey documentation.',
            ]);

            // Milestone 2: Hardware Installation
            $i2 = Intervention::create([
                'project_site_id' => $s1->id,
                'intervention_type' => 'hardware_installation',
                'previous_status' => 'survey_conducted',
                'new_status' => 'installed',
                'occurred_at' => now()->subDays(30),
                'created_by' => $admin->id,
                'source' => 'web_form',
            ]);
            $r2 = FieldReport::create([
                'intervention_id' => $i2->id,
                'notes' => 'Hardware mounting and cabling completed. 4 enterprise APs configured and connected to gigabit PoE switch.',
                'submission_lat' => 17.613210,
                'submission_lng' => 121.727020,
                'gps_accuracy_m' => 3.5,
                'device_timestamp' => now()->subDays(30),
                'server_timestamp' => now()->subDays(30),
                'custom_values' => [
                    'equipment_serial' => 'C9300-48P-R201',
                    'bandwidth_mbps' => 100,
                    'ap_count' => 4,
                ],
                'verification_status' => 'verified',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(29),
            ]);
            Attachment::create([
                'field_report_id' => $r2->id,
                'file_path' => '/storage/evidence/sample_install.jpg',
                'file_name' => 'tuguegarao_rack_install.jpg',
                'mime_type' => 'image/jpeg',
                'file_size_bytes' => 184520,
                'sha256_hash' => hash('sha256', 'mock_tuguegarao_rack_install'),
                'exif_lat' => 17.613210,
                'exif_lng' => 121.727020,
                'exif_timestamp' => now()->subDays(30),
            ]);

            // Milestone 3: QA & Go-Live
            $i3 = Intervention::create([
                'project_site_id' => $s1->id,
                'intervention_type' => 'qa_inspection',
                'previous_status' => 'installed',
                'new_status' => 'operational',
                'occurred_at' => now()->subDays(10),
                'created_by' => $admin->id,
                'source' => 'web_form',
            ]);
            FieldReport::create([
                'intervention_id' => $i3->id,
                'notes' => 'Quality assurance inspection concluded. Measured speedtest: 98.4 Mbps download, 92.1 Mbps upload, 8ms latency. Captive portal functioning properly.',
                'submission_lat' => 17.613205,
                'submission_lng' => 121.727010,
                'gps_accuracy_m' => 2.8,
                'device_timestamp' => now()->subDays(10),
                'server_timestamp' => now()->subDays(10),
                'custom_values' => [
                    'measured_download_mbps' => 98.4,
                ],
                'verification_status' => 'verified',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(9),
                'review_notes' => 'All benchmarks passed. Commissioning authorized.',
            ]);
        }

        // 2. Basco Municipal Hall Site
        $s2 = ProjectSite::where('site_code', 'FW4A-BAT-002')->first();
        if ($s2) {
            $ib1 = Intervention::create([
                'project_site_id' => $s2->id,
                'intervention_type' => 'hardware_installation',
                'previous_status' => 'planned',
                'new_status' => 'operational',
                'occurred_at' => now()->subDays(20),
                'created_by' => $admin->id,
                'source' => 'web_form',
            ]);
            FieldReport::create([
                'intervention_id' => $ib1->id,
                'notes' => 'High-Performance Starlink satellite terminal anchored to municipal rooftop mast with surge protection.',
                'submission_lat' => 20.448500,
                'submission_lng' => 121.970800,
                'gps_accuracy_m' => 5.1,
                'device_timestamp' => now()->subDays(20),
                'server_timestamp' => now()->subDays(20),
                'custom_values' => [
                    'isp_provider' => 'Starlink High Performance',
                    'bandwidth_mbps' => 220,
                    'ap_count' => 2,
                    'equipment_serial' => 'SL-UT-2026-BAT',
                ],
                'verification_status' => 'verified',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(19),
            ]);
        }

        // 3. Ilagan Government Center Site
        $s3 = ProjectSite::where('site_code', 'FW4A-ISA-003')->first();
        if ($s3) {
            $ii1 = Intervention::create([
                'project_site_id' => $s3->id,
                'intervention_type' => 'hardware_installation',
                'previous_status' => 'planned',
                'new_status' => 'installed',
                'occurred_at' => now()->subDays(5),
                'created_by' => $admin->id,
                'source' => 'web_form',
            ]);
            FieldReport::create([
                'intervention_id' => $ii1->id,
                'notes' => 'Converge fiber drop terminated. 6 APs deployed across north and south wings of Ilagan Government Center. Awaiting joint final acceptance.',
                'submission_lat' => 17.148500,
                'submission_lng' => 121.888500,
                'gps_accuracy_m' => 4.0,
                'device_timestamp' => now()->subDays(5),
                'server_timestamp' => now()->subDays(5),
                'custom_values' => [
                    'isp_provider' => 'Converge ICT',
                    'bandwidth_mbps' => 150,
                    'ap_count' => 6,
                ],
                'verification_status' => 'pending',
            ]);
        }

        // 4. Pending & Flagged submission test cases
        $s4 = ProjectSite::where('site_code', 'FW4A-NV-004')->first();
        if ($s4) {
            $inv4 = Intervention::create([
                'project_site_id' => $s4->id,
                'intervention_type' => 'site_survey',
                'previous_status' => 'planned',
                'new_status' => 'survey_conducted',
                'occurred_at' => now()->subDays(2),
                'created_by' => $admin->id,
                'source' => 'web_form',
            ]);
            FieldReport::create([
                'intervention_id' => $inv4->id,
                'notes' => 'Survey completed from Capitol park entrance. Proximity is borderline.',
                'submission_lat' => 17.000000, // Deliberately divergent to trigger flag test case
                'submission_lng' => 121.000000,
                'gps_accuracy_m' => 15.0,
                'device_timestamp' => now()->subDays(2),
                'server_timestamp' => now()->subDays(2),
                'verification_status' => 'flagged',
                'verification_flags' => ['OUTSIDE_SITE_PROXIMITY'],
            ]);
        }
    }
}
