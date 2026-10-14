<?php

namespace Database\Seeders;

use App\Domain\Portfolio\FieldDef;
use App\Domain\Portfolio\IndicatorTarget;
use App\Domain\Portfolio\InterventionType;
use App\Domain\Portfolio\Program;
use App\Domain\Portfolio\Project;
use App\Domain\Portfolio\ProjectScope;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed the 29 DICT Region 2 Programs
        $programs = [
            ['name' => 'Free Wi-Fi for All - Public Places', 'owner_unit' => 'NIPPSB', 'color' => '#2563eb', 'icon' => 'wifi', 'description' => 'Public Internet access in plazas, parks, RHUs, and transport terminals across Region 2.'],
            ['name' => 'National Broadband Program (NBP)', 'owner_unit' => 'NIPPSB', 'color' => '#1d4ed8', 'icon' => 'globe', 'description' => 'National fiber backbone connectivity and regional PoP expansion.'],
            ['name' => 'Government Network (GovNet)', 'owner_unit' => 'NIPPSB', 'color' => '#0284c7', 'icon' => 'network', 'description' => 'High-speed government fiber network linking regional line agencies in Tuguegarao Carig Center.'],
            ['name' => 'PIALEOS (Automated Observation System)', 'owner_unit' => 'NIPPSB', 'color' => '#0369a1', 'icon' => 'radar', 'description' => 'Maritime, weather, and land observation telemetry sensor network.'],
            ['name' => 'LEO Satellite / Starlink Connectivity for GIDA', 'owner_unit' => 'NIPPSB', 'color' => '#0ea5e9', 'icon' => 'satellite', 'description' => 'Low Earth Orbit satellite ground stations deployed in isolated island and mountain barangays.'],
            ['name' => 'Tech4ED / Digital Transformation Centers', 'owner_unit' => 'ILCDB', 'color' => '#4f46e5', 'icon' => 'monitor', 'description' => 'Community e-centers providing digital tools, literacy, and government transactional access.'],
            ['name' => 'Emergency Telecommunications Cluster (ETC)', 'owner_unit' => 'DRRM', 'color' => '#b91c1c', 'icon' => 'radio', 'description' => 'Rapidly deployable VHF/HF radio networks and mobile satellite terminals during typhoon response.'],
            ['name' => 'e-LGU (Local Government Digital System)', 'owner_unit' => 'e-Gov', 'color' => '#059669', 'icon' => 'building-library', 'description' => 'Unified LGU system covering BPLS, building permits, civil registry, and real property tax.'],
            ['name' => 'eGovPay (Government Payment Gateway)', 'owner_unit' => 'e-Gov', 'color' => '#10b981', 'icon' => 'credit-card', 'description' => 'Centralized payment gateway integration for municipal treasuries and provincial offices.'],
            ['name' => 'eGovPH Super App LGU Integration', 'owner_unit' => 'e-Gov', 'color' => '#14b8a6', 'icon' => 'device-phone', 'description' => 'Onboarding of Region 2 local government services into the national citizen mobile super app.'],
            ['name' => 'GovMail (National Government Email)', 'owner_unit' => 'IIDB', 'color' => '#0d9488', 'icon' => 'mail', 'description' => 'Official government domain email hosting and synchronization for regional employees.'],
            ['name' => 'GovCloud Adoption', 'owner_unit' => 'IIDB', 'color' => '#0891b2', 'icon' => 'cloud', 'description' => 'Migration of legacy LGU servers and data to the secured Government Cloud infrastructure.'],
            ['name' => 'PNPKI Digital Signatures', 'owner_unit' => 'Cybersecurity', 'color' => '#4338ca', 'icon' => 'key', 'description' => 'Issuance and deployment of digital certificates for government officials and secure document workflows.'],
            ['name' => 'eHealth & iClinicSys Teleconsultation Connectivity', 'owner_unit' => 'e-Gov', 'color' => '#047857', 'icon' => 'heart', 'description' => 'Rural Health Unit (RHU) digitization and PhilHealth e-claims network readiness.'],
            ['name' => 'SUC Campus Network Modernization', 'owner_unit' => 'IIDB', 'color' => '#6366f1', 'icon' => 'academic-cap', 'description' => 'Campus fiber and Wi-Fi coverage across CSU, ISU, NVSU, and QSU.'],
            ['name' => 'Barangay Connectivity & Portal Adoption', 'owner_unit' => 'e-Gov', 'color' => '#065f46', 'icon' => 'flag', 'description' => 'Official digital portal and transactional connectivity for barangay halls.'],
            ['name' => 'DigitalJobsPH (DJPH) Freelancing Training', 'owner_unit' => 'ILCDB', 'color' => '#d97706', 'icon' => 'briefcase', 'description' => 'Technical and freelance gig-economy training for youth, women, and out-of-school adults.'],
            ['name' => 'ILCDB ICT & AI Capacity Building', 'owner_unit' => 'ILCDB', 'color' => '#b45309', 'icon' => 'cpu-chip', 'description' => 'Advanced upskilling for government IT officers, teachers, and technical professionals.'],
            ['name' => 'Bayanihan SIM Registration Assistance', 'owner_unit' => 'Consumer Protection', 'color' => '#ca8a04', 'icon' => 'sim-card', 'description' => 'Assisted SIM card registration sweeps in remote municipalities and GIDA zones.'],
            ['name' => 'Cybersecurity Awareness & Hygiene Campaigns', 'owner_unit' => 'Cybersecurity', 'color' => '#9333ea', 'icon' => 'shield-check', 'description' => 'Public workshops and school caravans against phishing, online scams, and data breaches.'],
            ['name' => 'Girls in ICT & Digital Inclusion', 'owner_unit' => 'GAD', 'color' => '#db2777', 'icon' => 'user-group', 'description' => 'Gender and Development (GAD) targeted workshops empowering young women in tech.'],
            ['name' => 'Tech4All Senior Citizens & PWD Literacy', 'owner_unit' => 'ILCDB', 'color' => '#ea580c', 'icon' => 'hand-raised', 'description' => 'Adaptive digital accessibility training for senior citizens and persons with disabilities.'],
            ['name' => 'ICT Academy Student Certification Cohorts', 'owner_unit' => 'ILCDB', 'color' => '#c2410c', 'icon' => 'badge-check', 'description' => 'Industry-standard certification tracks (networking, cloud, python) in partnership with universities.'],
            ['name' => 'Startup Development & Incubation (TBIs)', 'owner_unit' => 'IIDB', 'color' => '#7c3aed', 'icon' => 'rocket', 'description' => 'Mentorship, seed staging, and hackathons supporting Cagayan Valley tech startups.'],
            ['name' => 'National ICT Household Survey (NICTHS)', 'owner_unit' => 'NIPPSB', 'color' => '#6d28d9', 'icon' => 'clipboard-document-list', 'description' => 'Regional household data collection benchmarking digital literacy and internet adoption.'],
            ['name' => 'National ICT Ecosystem Framework (NICTEF)', 'owner_unit' => 'Planning', 'color' => '#5b21b6', 'icon' => 'map', 'description' => 'Provincial digital transformation blueprints and ICT infrastructure masterplans.'],
            ['name' => 'Critical Information Infrastructure (CII) Audit', 'owner_unit' => 'Cybersecurity', 'color' => '#831843', 'icon' => 'lock-closed', 'description' => 'Vulnerability assessment and penetration testing for essential utility and health systems.'],
            ['name' => 'Carig RGC Fiber Expansion Feasibility Study', 'owner_unit' => 'NIPPSB', 'color' => '#475569', 'icon' => 'document-chart-bar', 'description' => 'Engineering survey for underground redundant ducting across Regional Government Center.'],
            ['name' => 'Spectrum & Tower Co-location Mapping', 'owner_unit' => 'NIPPSB', 'color' => '#334155', 'icon' => 'signal', 'description' => 'Radio frequency and cellular tower density registry across Region 2.'],
        ];

        $programMap = [];
        foreach ($programs as $prog) {
            $created = Program::updateOrCreate(['name' => $prog['name']], $prog);
            $programMap[$prog['name']] = $created;
        }

        // 2. Seed Default Project Types (The 4 Archetypes)
        // 2.1 Kind A: Site Deployment
        $typeSite = ProjectType::updateOrCreate(
            ['name' => 'Site Deployment (Kind A)'],
            [
                'kind' => 'site',
                'marker_shape' => 'circle',
                'color_code' => '#2563eb',
                'description' => 'Physical hardware and connectivity deployments with lifecycle installation and monitoring.',
            ]
        );

        $siteStatuses = [
            ['key' => 'planned', 'label' => 'Planned', 'sort_order' => 1, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['survey_conducted', 'cancelled']],
            ['key' => 'survey_conducted', 'label' => 'Survey Conducted', 'sort_order' => 2, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['equipment_delivered', 'planned']],
            ['key' => 'equipment_delivered', 'label' => 'Equipment Delivered', 'sort_order' => 3, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['installed']],
            ['key' => 'installed', 'label' => 'Installed', 'sort_order' => 4, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['inspected', 'rectification']],
            ['key' => 'inspected', 'label' => 'Inspected / QA Passed', 'sort_order' => 5, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['operational', 'rectification']],
            ['key' => 'operational', 'label' => 'Operational / Online', 'sort_order' => 6, 'is_completed' => true, 'is_terminal' => false, 'next_keys' => ['maintenance', 'decommissioned']],
            ['key' => 'maintenance', 'label' => 'Under Maintenance', 'sort_order' => 7, 'is_completed' => true, 'is_terminal' => false, 'next_keys' => ['operational', 'decommissioned']],
            ['key' => 'rectification', 'label' => 'Rectification Needed', 'sort_order' => 8, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['inspected']],
            ['key' => 'decommissioned', 'label' => 'Decommissioned', 'sort_order' => 9, 'is_completed' => false, 'is_terminal' => true, 'next_keys' => []],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'sort_order' => 10, 'is_completed' => false, 'is_terminal' => true, 'next_keys' => []],
        ];

        foreach ($siteStatuses as $s) {
            StatusDef::updateOrCreate(['project_type_id' => $typeSite->id, 'key' => $s['key']], $s);
        }

        $siteInterventions = [
            ['key' => 'site_survey', 'label' => 'Site Feasibility Survey'],
            ['key' => 'hardware_installation', 'label' => 'Hardware Mounting & Wiring'],
            ['key' => 'qa_inspection', 'label' => 'Quality Assurance Inspection'],
            ['key' => 'preventive_maintenance', 'label' => 'Routine Preventive Maintenance'],
            ['key' => 'restoration_repair', 'label' => 'Outage Restoration & Repair'],
            ['key' => 'speed_test', 'label' => 'Bandwidth & Latency Benchmark'],
        ];
        foreach ($siteInterventions as $inv) {
            InterventionType::updateOrCreate(['project_type_id' => $typeSite->id, 'key' => $inv['key']], $inv);
        }

        $siteFields = [
            ['key' => 'isp_provider', 'label' => 'ISP / Telco Partner', 'type' => 'text', 'required' => true, 'applies_to' => 'site', 'sort_order' => 1],
            ['key' => 'bandwidth_mbps', 'label' => 'Committed Bandwidth (Mbps)', 'type' => 'integer', 'required' => true, 'applies_to' => 'site', 'sort_order' => 2],
            ['key' => 'ap_count', 'label' => 'Access Point Count', 'type' => 'integer', 'required' => true, 'applies_to' => 'site', 'sort_order' => 3],
            ['key' => 'equipment_serial', 'label' => 'Primary Router/AP Serial', 'type' => 'text', 'required' => false, 'applies_to' => 'site', 'sort_order' => 4],
            ['key' => 'measured_download_mbps', 'label' => 'Measured Download Speed (Mbps)', 'type' => 'number', 'required' => false, 'applies_to' => 'report', 'sort_order' => 5],
        ];
        foreach ($siteFields as $f) {
            FieldDef::updateOrCreate(['project_type_id' => $typeSite->id, 'key' => $f['key']], $f);
        }

        // 2.2 Kind B: Institution Adoption
        $typeInst = ProjectType::updateOrCreate(
            ['name' => 'Institution Adoption (Kind B)'],
            [
                'kind' => 'institution',
                'marker_shape' => 'square',
                'color_code' => '#059669',
                'description' => 'Institutional rollout and system adoption by LGUs, SUCs, hospitals, and line agencies.',
            ]
        );

        $instStatuses = [
            ['key' => 'targeted', 'label' => 'Targeted / Scoped', 'sort_order' => 1, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['mou_drafted', 'dropped']],
            ['key' => 'mou_drafted', 'label' => 'MOU Drafted', 'sort_order' => 2, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['mou_signed', 'targeted']],
            ['key' => 'mou_signed', 'label' => 'MOU Signed & Executed', 'sort_order' => 3, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['system_provisioned']],
            ['key' => 'system_provisioned', 'label' => 'System Cloud Provisioned', 'sort_order' => 4, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['staff_trained']],
            ['key' => 'staff_trained', 'label' => 'Staff Trained & Certified', 'sort_order' => 5, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['live_production']],
            ['key' => 'live_production', 'label' => 'Live in Production', 'sort_order' => 6, 'is_completed' => true, 'is_terminal' => false, 'next_keys' => ['fully_adopted']],
            ['key' => 'fully_adopted', 'label' => 'Fully Adopted & Integrated', 'sort_order' => 7, 'is_completed' => true, 'is_terminal' => true, 'next_keys' => []],
            ['key' => 'dropped', 'label' => 'Withdrawn / Dropped', 'sort_order' => 8, 'is_completed' => false, 'is_terminal' => true, 'next_keys' => []],
        ];
        foreach ($instStatuses as $s) {
            StatusDef::updateOrCreate(['project_type_id' => $typeInst->id, 'key' => $s['key']], $s);
        }

        $instInterventions = [
            ['key' => 'stakeholder_meeting', 'label' => 'Mayoral / Leadership Meeting'],
            ['key' => 'mou_ceremony', 'label' => 'Formal MOU Signing Ceremony'],
            ['key' => 'tenant_setup', 'label' => 'Cloud Tenant & Domain Provisioning'],
            ['key' => 'user_enablement', 'label' => 'Local Staff System Orientation'],
            ['key' => 'system_launch', 'label' => 'Public Go-Live Launch'],
        ];
        foreach ($instInterventions as $inv) {
            InterventionType::updateOrCreate(['project_type_id' => $typeInst->id, 'key' => $inv['key']], $inv);
        }

        $instFields = [
            ['key' => 'focal_person_name', 'label' => 'Focal Officer Name', 'type' => 'text', 'required' => true, 'applies_to' => 'site', 'sort_order' => 1],
            ['key' => 'focal_person_contact', 'label' => 'Contact Number / Mobile', 'type' => 'text', 'required' => true, 'applies_to' => 'site', 'sort_order' => 2],
            ['key' => 'adopted_modules', 'label' => 'Active System Modules', 'type' => 'multiselect', 'options' => ['Business Permit (BPLS)', 'Building Permit', 'Civil Registry', 'Real Property Tax', 'e-Payment'], 'required' => false, 'applies_to' => 'report', 'sort_order' => 3],
        ];
        foreach ($instFields as $f) {
            FieldDef::updateOrCreate(['project_type_id' => $typeInst->id, 'key' => $f['key']], $f);
        }

        // 2.3 Kind C: Activities & Beneficiaries
        $typeAct = ProjectType::updateOrCreate(
            ['name' => 'Capacity & Training (Kind C)'],
            [
                'kind' => 'activity',
                'marker_shape' => 'diamond',
                'color_code' => '#d97706',
                'description' => 'Training cohorts, workshops, and beneficiary distribution initiatives with GAD data.',
            ]
        );

        $actStatuses = [
            ['key' => 'scheduled', 'label' => 'Scheduled', 'sort_order' => 1, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['registration_open', 'cancelled']],
            ['key' => 'registration_open', 'label' => 'Registration Open', 'sort_order' => 2, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['training_ongoing']],
            ['key' => 'training_ongoing', 'label' => 'Training In Progress', 'sort_order' => 3, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['training_concluded']],
            ['key' => 'training_concluded', 'label' => 'Training Concluded', 'sort_order' => 4, 'is_completed' => true, 'is_terminal' => false, 'next_keys' => ['graduated_certified']],
            ['key' => 'graduated_certified', 'label' => 'Graduated & Certified', 'sort_order' => 5, 'is_completed' => true, 'is_terminal' => true, 'next_keys' => []],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'sort_order' => 6, 'is_completed' => false, 'is_terminal' => true, 'next_keys' => []],
        ];
        foreach ($actStatuses as $s) {
            StatusDef::updateOrCreate(['project_type_id' => $typeAct->id, 'key' => $s['key']], $s);
        }

        $actInterventions = [
            ['key' => 'cohort_orientation', 'label' => 'Course Orientation & Onboarding'],
            ['key' => 'hands_on_mentorship', 'label' => 'Hands-on Mentorship Lab'],
            ['key' => 'capstone_defense', 'label' => 'Portfolio / Capstone Defense'],
            ['key' => 'graduation_ceremony', 'label' => 'Graduation & Certification Conferment'],
        ];
        foreach ($actInterventions as $inv) {
            InterventionType::updateOrCreate(['project_type_id' => $typeAct->id, 'key' => $inv['key']], $inv);
        }

        $actFields = [
            ['key' => 'course_track', 'label' => 'Course Specialization', 'type' => 'select', 'options' => ['Virtual Assistance', 'Web Development', 'Content Writing & SEO', 'Graphic Design & Video Editing', 'Cybersecurity Fundamentals'], 'required' => true, 'applies_to' => 'activity', 'sort_order' => 1],
            ['key' => 'participants_gender', 'label' => 'Sex-Disaggregated Breakdown (M/F)', 'type' => 'sex_disaggregated', 'required' => true, 'applies_to' => 'activity', 'sort_order' => 2],
            ['key' => 'pwd_participants', 'label' => 'PWD Participants Count', 'type' => 'integer', 'required' => false, 'applies_to' => 'activity', 'sort_order' => 3],
        ];
        foreach ($actFields as $f) {
            FieldDef::updateOrCreate(['project_type_id' => $typeAct->id, 'key' => $f['key']], $f);
        }

        // 2.4 Kind D: Milestones & Studies
        $typeMile = ProjectType::updateOrCreate(
            ['name' => 'Milestones & Studies (Kind D)'],
            [
                'kind' => 'milestone',
                'marker_shape' => 'triangle',
                'color_code' => '#7c3aed',
                'description' => 'Research surveys, regional roadmaps, and cybersecurity audits without fixed point sites.',
            ]
        );

        $mileStatuses = [
            ['key' => 'scoping', 'label' => 'Scoping & Framework', 'sort_order' => 1, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['field_work', 'shelved']],
            ['key' => 'field_work', 'label' => 'Field Work & Data Sweep', 'sort_order' => 2, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['synthesis_draft']],
            ['key' => 'synthesis_draft', 'label' => 'Synthesis & Review Draft', 'sort_order' => 3, 'is_completed' => false, 'is_terminal' => false, 'next_keys' => ['final_published']],
            ['key' => 'final_published', 'label' => 'Final Output Published', 'sort_order' => 4, 'is_completed' => true, 'is_terminal' => true, 'next_keys' => []],
            ['key' => 'shelved', 'label' => 'Shelved / Deferred', 'sort_order' => 5, 'is_completed' => false, 'is_terminal' => true, 'next_keys' => []],
        ];
        foreach ($mileStatuses as $s) {
            StatusDef::updateOrCreate(['project_type_id' => $typeMile->id, 'key' => $s['key']], $s);
        }

        $mileInterventions = [
            ['key' => 'consultation_workshop', 'label' => 'Regional Consultation Workshop'],
            ['key' => 'field_data_audit', 'label' => 'Field Enumeration & Sampling'],
            ['key' => 'executive_presentation', 'label' => 'RDC / Executive Committee Presentation'],
        ];
        foreach ($mileInterventions as $inv) {
            InterventionType::updateOrCreate(['project_type_id' => $typeMile->id, 'key' => $inv['key']], $inv);
        }

        $mileFields = [
            ['key' => 'lead_investigator', 'label' => 'Project Lead / Author', 'type' => 'text', 'required' => true, 'applies_to' => 'report', 'sort_order' => 1],
            ['key' => 'document_repository_url', 'label' => 'Document Repository Link', 'type' => 'text', 'required' => false, 'applies_to' => 'report', 'sort_order' => 2],
        ];
        foreach ($mileFields as $f) {
            FieldDef::updateOrCreate(['project_type_id' => $typeMile->id, 'key' => $f['key']], $f);
        }

        // 3. Seed Concrete Sample Projects for Immediate Rich Showcase
        // 3.1 Project 1: Free Wi-Fi 2026 Phase 1
        $p1 = Project::updateOrCreate(
            ['code' => 'FW4A-2026-R2-P1'],
            [
                'name' => 'Free Wi-Fi for All 2026 Phase 1 - Cagayan & Isabela',
                'program_id' => $programMap['Free Wi-Fi for All - Public Places']->id,
                'project_type_id' => $typeSite->id,
                'lifecycle' => 'active',
                'budget' => 18500000.00,
                'funding_source' => 'GAA 2026 Free Wi-Fi Fund',
                'start_date' => '2026-01-15',
                'end_date' => '2026-12-31',
                'color' => '#2563eb',
            ]
        );
        // Scopes: Cagayan (0215000000) and Isabela (0231000000)
        ProjectScope::updateOrCreate(['project_id' => $p1->id, 'psgc_code' => '0215000000']);
        ProjectScope::updateOrCreate(['project_id' => $p1->id, 'psgc_code' => '0231000000']);
        // Targets: 150 Hotspots in 2026
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p1->id, 'indicator_key' => 'operational_hotspots', 'psgc_code' => null, 'period' => '2026'],
            ['target_value' => 150]
        );
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p1->id, 'indicator_key' => 'operational_hotspots', 'psgc_code' => '0215000000', 'period' => '2026'],
            ['target_value' => 80]
        );
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p1->id, 'indicator_key' => 'operational_hotspots', 'psgc_code' => '0231000000', 'period' => '2026'],
            ['target_value' => 70]
        );

        // 3.2 Project 2: eLGU Rollout Region 2
        $p2 = Project::updateOrCreate(
            ['code' => 'ELGU-2026-R2'],
            [
                'name' => 'eLGU Unified Digital Governance Rollout Region 2',
                'program_id' => $programMap['e-LGU (Local Government Digital System)']->id,
                'project_type_id' => $typeInst->id,
                'lifecycle' => 'active',
                'budget' => 9200000.00,
                'funding_source' => 'e-Gov Trust Fund',
                'start_date' => '2026-02-01',
                'end_date' => '2026-11-30',
                'color' => '#059669',
            ]
        );
        // Scopes: All 5 provinces
        foreach (['0209000000', '0215000000', '0231000000', '0250000000', '0257000000'] as $provCode) {
            ProjectScope::updateOrCreate(['project_id' => $p2->id, 'psgc_code' => $provCode]);
        }
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p2->id, 'indicator_key' => 'onboarded_lgus', 'psgc_code' => null, 'period' => '2026'],
            ['target_value' => 45]
        );

        // 3.3 Project 3: DigitalJobsPH Cohort 2026
        $p3 = Project::updateOrCreate(
            ['code' => 'DJPH-2026-COHORT1'],
            [
                'name' => 'DigitalJobsPH Freelancing & Tech Upskilling 2026',
                'program_id' => $programMap['DigitalJobsPH (DJPH) Freelancing Training']->id,
                'project_type_id' => $typeAct->id,
                'lifecycle' => 'active',
                'budget' => 4500000.00,
                'funding_source' => 'GAA 2026 ILCDB Fund',
                'start_date' => '2026-03-01',
                'end_date' => '2026-10-31',
                'color' => '#d97706',
            ]
        );
        foreach (['0215000000', '0231000000', '0250000000', '0257000000'] as $provCode) {
            ProjectScope::updateOrCreate(['project_id' => $p3->id, 'psgc_code' => $provCode]);
        }
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p3->id, 'indicator_key' => 'certified_graduates', 'psgc_code' => null, 'period' => '2026'],
            ['target_value' => 600]
        );

        // 3.4 Project 4: GovNet Carig Regional Backbone
        $p4 = Project::updateOrCreate(
            ['code' => 'GOVNET-TUG-2026'],
            [
                'name' => 'GovNet Tuguegarao Regional Government Center Expansion',
                'program_id' => $programMap['Government Network (GovNet)']->id,
                'project_type_id' => $typeSite->id,
                'lifecycle' => 'active',
                'budget' => 25000000.00,
                'funding_source' => 'DICT Capital Outlay',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'color' => '#0284c7',
            ]
        );
        ProjectScope::updateOrCreate(['project_id' => $p4->id, 'psgc_code' => '0215290000']); // Tuguegarao City
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p4->id, 'indicator_key' => 'connected_buildings', 'psgc_code' => '0215290000', 'period' => '2026'],
            ['target_value' => 38]
        );

        // 3.5 Project 5: NICTHS Regional Sweep
        $p5 = Project::updateOrCreate(
            ['code' => 'NICTHS-R2-2026'],
            [
                'name' => 'Region 2 ICT Household & Readiness Study 2026',
                'program_id' => $programMap['National ICT Household Survey (NICTHS)']->id,
                'project_type_id' => $typeMile->id,
                'lifecycle' => 'active',
                'budget' => 3200000.00,
                'funding_source' => 'DICT Research Fund',
                'start_date' => '2026-02-15',
                'end_date' => '2026-09-30',
                'color' => '#6d28d9',
            ]
        );
        ProjectScope::updateOrCreate(['project_id' => $p5->id, 'psgc_code' => '0200000000']); // Regionwide
        IndicatorTarget::updateOrCreate(
            ['project_id' => $p5->id, 'indicator_key' => 'barangays_enumerated', 'psgc_code' => null, 'period' => '2026'],
            ['target_value' => 350]
        );
    }
}
