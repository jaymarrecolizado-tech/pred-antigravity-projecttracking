<?php

namespace Database\Seeders;

use App\Domain\Portfolio\Project;
use App\Domain\Registry\Institution;
use App\Domain\Registry\Location;
use App\Domain\Registry\ProjectSite;
use Illuminate\Database\Seeder;

class RegistrySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Shared Physical Locations across Region 2
        $locations = [
            [
                'name' => 'Basco Municipal Hall Plaza',
                'category' => 'plaza',
                'psgc_barangay' => '020901001',
                'lat' => 20.4485,
                'lng' => 121.9708,
                'elevation_m' => 14.5,
                'is_verified' => true,
                'address_details' => 'National Road, Kayvaluganan, Basco, Batanes',
            ],
            [
                'name' => 'Basco Central Elementary School',
                'category' => 'school',
                'psgc_barangay' => '020901001',
                'lat' => 20.4470,
                'lng' => 121.9690,
                'elevation_m' => 12.0,
                'is_verified' => true,
                'address_details' => 'School Zone, Kayvaluganan, Basco, Batanes',
            ],
            [
                'name' => 'Tuguegarao City Hall Plaza',
                'category' => 'plaza',
                'psgc_barangay' => '021528001',
                'lat' => 17.6132,
                'lng' => 121.7270,
                'elevation_m' => 18.2,
                'is_verified' => true,
                'address_details' => 'Rizal St., Centro 01, Tuguegarao City, Cagayan',
            ],
            [
                'name' => 'Cagayan Valley Medical Center',
                'category' => 'hospital_clinic',
                'psgc_barangay' => '021528001',
                'lat' => 17.6521,
                'lng' => 121.7512,
                'elevation_m' => 24.0,
                'is_verified' => true,
                'address_details' => 'Regional Government Center, Carig Sur, Tuguegarao City',
            ],
            [
                'name' => 'Cagayan State University - Andrews Campus',
                'category' => 'school',
                'psgc_barangay' => '021528001',
                'lat' => 17.6180,
                'lng' => 121.7225,
                'elevation_m' => 19.5,
                'is_verified' => true,
                'address_details' => 'Caritan Centro, Tuguegarao City, Cagayan',
            ],
            [
                'name' => 'Regional Government Center (Carig Hub)',
                'category' => 'lgu_hall',
                'psgc_barangay' => '021528001',
                'lat' => 17.6490,
                'lng' => 121.7530,
                'elevation_m' => 26.0,
                'is_verified' => true,
                'address_details' => 'Carig Sur, Tuguegarao City, Cagayan',
            ],
            [
                'name' => 'City of Ilagan Government Center',
                'category' => 'lgu_hall',
                'psgc_barangay' => '023114001',
                'lat' => 17.1485,
                'lng' => 121.8885,
                'elevation_m' => 45.0,
                'is_verified' => true,
                'address_details' => 'San Vicente, City of Ilagan, Isabela',
            ],
            [
                'name' => 'Isabela State University - Main Campus',
                'category' => 'school',
                'psgc_barangay' => '023111001',
                'lat' => 16.7120,
                'lng' => 121.6840,
                'elevation_m' => 88.0,
                'is_verified' => true,
                'address_details' => 'San Fabian, Echague, Isabela',
            ],
            [
                'name' => 'Nueva Vizcaya Provincial Capitol',
                'category' => 'lgu_hall',
                'psgc_barangay' => '025004001',
                'lat' => 16.4820,
                'lng' => 121.1460,
                'elevation_m' => 295.0,
                'is_verified' => true,
                'address_details' => 'Capitol Compound, District IV, Bayombong, Nueva Vizcaya',
            ],
            [
                'name' => 'Quirino Provincial Hospital Compound',
                'category' => 'hospital_clinic',
                'psgc_barangay' => '025701001',
                'lat' => 16.5240,
                'lng' => 121.5180,
                'elevation_m' => 135.0,
                'is_verified' => true,
                'address_details' => 'Zamora, Cabarroguis, Quirino',
            ],
        ];

        $locationMap = [];
        foreach ($locations as $locData) {
            $loc = Location::updateOrCreate(
                ['name' => $locData['name']],
                $locData
            );
            $locationMap[$locData['name']] = $loc;
        }

        // 2. Seed Institutions
        $institutions = [
            [
                'kind' => 'lgu',
                'name' => 'City Government of Tuguegarao',
                'external_id' => 'LGU-021528',
                'psgc_code' => '021528000',
                'primary_location_id' => $locationMap['Tuguegarao City Hall Plaza']->id,
                'contact_person' => 'Hon. Mayor Office',
                'contact_email' => 'mayor@tuguegaraocity.gov.ph',
            ],
            [
                'kind' => 'lgu',
                'name' => 'Municipality of Basco',
                'external_id' => 'LGU-020901',
                'psgc_code' => '020901000',
                'primary_location_id' => $locationMap['Basco Municipal Hall Plaza']->id,
                'contact_person' => 'Municipal Administrator',
                'contact_email' => 'admin@basco-batanes.gov.ph',
            ],
            [
                'kind' => 'lgu',
                'name' => 'City Government of Ilagan',
                'external_id' => 'LGU-023114',
                'psgc_code' => '023114000',
                'primary_location_id' => $locationMap['City of Ilagan Government Center']->id,
                'contact_person' => 'City Planning & Development Office',
                'contact_email' => 'cpdo@cityofilagan.gov.ph',
            ],
            [
                'kind' => 'suc',
                'name' => 'Cagayan State University System',
                'external_id' => 'SUC-CSU-R2',
                'psgc_code' => '021528000',
                'primary_location_id' => $locationMap['Cagayan State University - Andrews Campus']->id,
                'contact_person' => 'University MIS Director',
                'contact_email' => 'mis@csu.edu.ph',
            ],
            [
                'kind' => 'suc',
                'name' => 'Isabela State University',
                'external_id' => 'SUC-ISU-R2',
                'psgc_code' => '023111000',
                'primary_location_id' => $locationMap['Isabela State University - Main Campus']->id,
                'contact_person' => 'ICT Director',
                'contact_email' => 'ict@isu.edu.ph',
            ],
            [
                'kind' => 'deped_school',
                'name' => 'Basco Central Elementary School',
                'external_id' => 'DEPED-102931',
                'psgc_code' => '020901000',
                'primary_location_id' => $locationMap['Basco Central Elementary School']->id,
                'contact_person' => 'Principal Office',
                'contact_email' => 'basco.central@deped.gov.ph',
            ],
            [
                'kind' => 'hospital_clinic',
                'name' => 'Cagayan Valley Medical Center',
                'external_id' => 'DOH-CVMC-02',
                'psgc_code' => '021528000',
                'primary_location_id' => $locationMap['Cagayan Valley Medical Center']->id,
                'contact_person' => 'Medical Center Chief',
                'contact_email' => 'info@cvmc.doh.gov.ph',
            ],
        ];

        $institutionMap = [];
        foreach ($institutions as $instData) {
            $inst = Institution::updateOrCreate(
                ['name' => $instData['name']],
                $instData
            );
            $institutionMap[$instData['name']] = $inst;
        }

        // 3. Enroll Sites into Active Projects
        $fw4a = Project::where('code', 'FW4A-2026-R2-P1')->first();
        if ($fw4a) {
            ProjectSite::updateOrCreate(
                ['project_id' => $fw4a->id, 'site_code' => 'FW4A-CAG-001'],
                [
                    'location_id' => $locationMap['Tuguegarao City Hall Plaza']->id,
                    'current_status' => 'operational',
                    'target_completion_date' => '2026-03-31',
                    'custom_attributes' => [
                        'isp_provider' => 'PLDT Enterprise',
                        'bandwidth_mbps' => 100,
                        'ap_count' => 4,
                        'equipment_serial' => 'C9300-48P-R201',
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $fw4a->id, 'site_code' => 'FW4A-BAT-002'],
                [
                    'location_id' => $locationMap['Basco Municipal Hall Plaza']->id,
                    'current_status' => 'operational',
                    'target_completion_date' => '2026-04-15',
                    'custom_attributes' => [
                        'isp_provider' => 'Starlink High Performance',
                        'bandwidth_mbps' => 220,
                        'ap_count' => 2,
                        'equipment_serial' => 'SL-UT-2026-BAT',
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $fw4a->id, 'site_code' => 'FW4A-ISA-003'],
                [
                    'location_id' => $locationMap['City of Ilagan Government Center']->id,
                    'current_status' => 'installed',
                    'target_completion_date' => '2026-06-30',
                    'custom_attributes' => [
                        'isp_provider' => 'Converge ICT',
                        'bandwidth_mbps' => 150,
                        'ap_count' => 6,
                        'equipment_serial' => 'CVG-GW-49102',
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $fw4a->id, 'site_code' => 'FW4A-NV-004'],
                [
                    'location_id' => $locationMap['Nueva Vizcaya Provincial Capitol']->id,
                    'current_status' => 'survey_conducted',
                    'target_completion_date' => '2026-08-31',
                    'custom_attributes' => [
                        'isp_provider' => 'Globe Telecom',
                        'bandwidth_mbps' => 100,
                        'ap_count' => 3,
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $fw4a->id, 'site_code' => 'FW4A-QUI-005'],
                [
                    'location_id' => $locationMap['Quirino Provincial Hospital Compound']->id,
                    'current_status' => 'planned',
                    'target_completion_date' => '2026-10-31',
                    'custom_attributes' => [
                        'isp_provider' => 'PLDT / Smart',
                        'bandwidth_mbps' => 50,
                        'ap_count' => 2,
                    ],
                ]
            );
        }

        $elgu = Project::where('code', 'ELGU-2026-R2')->first();
        if ($elgu) {
            ProjectSite::updateOrCreate(
                ['project_id' => $elgu->id, 'site_code' => 'ELGU-TUG-01'],
                [
                    'institution_id' => $institutionMap['City Government of Tuguegarao']->id,
                    'location_id' => $locationMap['Tuguegarao City Hall Plaza']->id,
                    'current_status' => 'live_production',
                    'target_completion_date' => '2026-05-31',
                    'custom_attributes' => [
                        'focal_person_name' => 'Engr. Juan Dela Cruz',
                        'focal_person_contact' => '0917-123-4567',
                        'adopted_modules' => ['Business Permit (BPLS)', 'Building Permit', 'Civil Registry'],
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $elgu->id, 'site_code' => 'ELGU-BAS-02'],
                [
                    'institution_id' => $institutionMap['Municipality of Basco']->id,
                    'location_id' => $locationMap['Basco Municipal Hall Plaza']->id,
                    'current_status' => 'staff_trained',
                    'target_completion_date' => '2026-07-31',
                    'custom_attributes' => [
                        'focal_person_name' => 'Maria Castillejos',
                        'focal_person_contact' => '0998-765-4321',
                        'adopted_modules' => ['Business Permit (BPLS)', 'e-Payment'],
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $elgu->id, 'site_code' => 'ELGU-ILA-03'],
                [
                    'institution_id' => $institutionMap['City Government of Ilagan']->id,
                    'location_id' => $locationMap['City of Ilagan Government Center']->id,
                    'current_status' => 'mou_signed',
                    'target_completion_date' => '2026-09-30',
                    'custom_attributes' => [
                        'focal_person_name' => 'Atty. Roberto Santos',
                        'focal_person_contact' => '0919-555-8888',
                        'adopted_modules' => ['Business Permit (BPLS)'],
                    ],
                ]
            );
        }

        $govnet = Project::where('code', 'GOVNET-TUG-2026')->first();
        if ($govnet) {
            ProjectSite::updateOrCreate(
                ['project_id' => $govnet->id, 'site_code' => 'GOVNET-CARIG-01'],
                [
                    'location_id' => $locationMap['Regional Government Center (Carig Hub)']->id,
                    'current_status' => 'operational',
                    'target_completion_date' => '2026-04-30',
                    'custom_attributes' => [
                        'isp_provider' => 'DICT GovNet Core Fiber',
                        'bandwidth_mbps' => 1000,
                        'ap_count' => 16,
                    ],
                ]
            );

            ProjectSite::updateOrCreate(
                ['project_id' => $govnet->id, 'site_code' => 'GOVNET-CSU-02'],
                [
                    'institution_id' => $institutionMap['Cagayan State University System']->id,
                    'location_id' => $locationMap['Cagayan State University - Andrews Campus']->id,
                    'current_status' => 'operational',
                    'target_completion_date' => '2026-05-15',
                    'custom_attributes' => [
                        'isp_provider' => 'DICT GovNet Core Fiber',
                        'bandwidth_mbps' => 500,
                        'ap_count' => 8,
                    ],
                ]
            );
        }
    }
}
