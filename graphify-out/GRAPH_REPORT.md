# Graph Report - pred-project-tracking  (2026-10-15)

## Corpus Check
- 150 files · ~518,547 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 966 nodes · 1855 edges · 97 communities (81 shown, 16 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 169 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `de488aad`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Http\Request
- User
- devDependencies
- BoundaryValidator
- PsgcArea
- Intervention
- ReportingAuditTest
- ProjectSite
- MapView.vue
- Map/Index.vue
- Projects/Create.vue
- Illuminate\Database\Eloquent\Relations\HasMany
- scripts
- Sites/Index.vue
- KpiCalculator
- TwoFactorSetup.vue
- Projects/Index.vue
- Reports/Index.vue
- .parent
- Edit.vue
- Sites/Create.vue
- Submissions/Create.vue
- ProjectType
- Geo/Index.vue
- Users/Index.vue
- Institutions/Index.vue
- ReportController.php
- composer.json
- require-dev
- UpdateRollupJob.php
- StatusDef
- StatusWorkflow
- require
- Dashboard.vue
- ReviewQueue.vue
- RollupDaily
- config
- Show.vue
- SiteRegistryTest
- AppShell.vue
- psr-4
- extra
- Audit/Index.vue
- TwoFactorChallenge.vue
- InteractiveMapTest
- ExampleTest
- Login.vue
- keywords
- ProjectTypes/Index.vue
- bootstrap/app.php
- REGION II SITE STATUS 2026_185a78b2.md
- DICT Region 2 Infrastructure & Program Rollout Monitoring Platform
- ProjectCrudTest
- UpdateUserProfileInformation.php
- Plan#8: Rollup Tables, Backlog Tracking & Analytics Engine
- Plan#6: Field Reporting, Timeline & Verification Engine
- Plan#2: Authentication, Scoped RBAC & 2FA Engine
- Plan#3: PSGC Geographic Hierarchy, Districts & Boundary Layers
- Plan#4: Configurable Multi-Project & Portfolio Engine
- Plan#5: Shared Location & Institution Registries
- Project Completion & Production Handover Summary
- Plan#7: Interactive Cartographic Map Engine (Leaflet + Supercluster)
- Plan#9: Reporting, Audit Trail & Automated Dispatch
- Plan#1: Foundational Design System & App Shell
- GeoTest
- ReconLogo.vue
- rules/graphify.md
- workflows/graphify.md

## God Nodes (most connected - your core abstractions)
1. `User` - 61 edges
2. `ProjectSite` - 53 edges
3. `ProjectType` - 45 edges
4. `Project` - 42 edges
5. `GrantResolver` - 36 edges
6. `Program` - 33 edges
7. `TestCase` - 30 edges
8. `PsgcArea` - 27 edges
9. `KpiCalculator` - 26 edges
10. `Location` - 26 edges

## Surprising Connections (you probably didn't know these)
- `ProjectCrudTest` --references--> `Program`  [EXTRACTED]
  tests/Feature/ProjectCrudTest.php → app/Domain/Portfolio/Program.php
- `AnalyticsRollupTest` --references--> `Project`  [EXTRACTED]
  tests/Feature/AnalyticsRollupTest.php → app/Domain/Portfolio/Project.php
- `InteractiveMapTest` --references--> `Project`  [EXTRACTED]
  tests/Feature/InteractiveMapTest.php → app/Domain/Portfolio/Project.php
- `ReportingAuditTest` --references--> `Project`  [EXTRACTED]
  tests/Feature/ReportingAuditTest.php → app/Domain/Portfolio/Project.php
- `SiteRegistryTest` --references--> `Project`  [EXTRACTED]
  tests/Feature/SiteRegistryTest.php → app/Domain/Portfolio/Project.php

## Import Cycles
- None detected.

## Communities (97 total, 16 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.05
Nodes (24): RoleGrant, CongressionalDistrict, DistrictMembership, IndicatorTarget, InterventionType, Program, Project, ProjectScope (+16 more)

### Community 1 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (18): AuditLog, FieldDef, ProjectType, AuditController, GeoController, ProjectTypeController, UserController, TwoFactorSetupController (+10 more)

### Community 2 - "User"
Cohesion: 0.11
Nodes (10): CreateNewUser, ResetUserPassword, UpdateUserPassword, AppServiceProvider, FortifyServiceProvider, Illuminate\Support\ServiceProvider, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords (+2 more)

### Community 3 - "devDependencies"
Cohesion: 0.06
Nodes (33): axios, concurrently, @inertiajs/vue3, laravel-vite-plugin, leaflet, mapshaper, allowScripts, esbuild@0.25.12 (+25 more)

### Community 4 - "BoundaryValidator"
Cohesion: 0.07
Nodes (7): BoundaryValidator, StatusWorkflow, SiteImportService, Attachment, FraudDetector, BoundaryValidatorTest, FraudDetectorTest

### Community 5 - "PsgcArea"
Cohesion: 0.22
Nodes (3): PsgcImport, PsgcArea, Illuminate\Database\Eloquent\Builder

### Community 6 - "Intervention"
Cohesion: 0.12
Nodes (6): Intervention, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Relations\HasOne, static, FieldSubmissionTest

### Community 7 - "ReportingAuditTest"
Cohesion: 0.14
Nodes (3): AuditLogger, ReportSubscription, ReportingAuditTest

### Community 9 - "MapView.vue"
Cohesion: 0.24
Nodes (15): createClusterIcon(), createSiteIcon(), getPercentageColor(), interpolateHex(), activeMetricLabel, emit, filteredFeatures, initMap() (+7 more)

### Community 10 - "Map/Index.vue"
Cohesion: 0.11
Nodes (10): activePointsCount, activeSelection, clusterMode, granularitySlider, props, selectedBasemap, selectedMetric, selectedProjectIds (+2 more)

### Community 11 - "Projects/Create.vue"
Cohesion: 0.13
Nodes (8): currentStep, expandedProvinces, form, generateCode(), getMunicipalitiesForProvince(), onProgramChange(), props, toggleProvince()

### Community 12 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.09
Nodes (20): 1. Prerequisites & Required PHP Extensions, 2. Initial Setup & Database Provisioning, 3. Background Queue Worker (Supervisor), 4. Automated Scheduled Cron Tasks, 5. Nginx Web Server Configuration, 6. Append-Only Integrity & Security Auditing, DICT Region 2 Infrastructure & Program Rollout Monitoring Platform, Enterprise Deployment & Operations Guide (`DEPLOYMENT.md`) (+12 more)

### Community 13 - "scripts"
Cohesion: 0.13
Nodes (15): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+7 more)

### Community 14 - "Sites/Index.vue"
Cohesion: 0.14
Nodes (12): applyFilters(), hasActiveFilters, importPayloadString, importProjectId, isImporting, props, resetFilters(), searchQuery (+4 more)

### Community 15 - "KpiCalculator"
Cohesion: 0.17
Nodes (5): KpiCalculator, Carbon, ProjectSite, DashboardController, Carbon\Carbon

### Community 16 - "TwoFactorSetup.vue"
Cohesion: 0.14
Nodes (8): confirmCode, confirmError, confirming, loading, props, qrCodeSvg, recoveryCodes, secretKey

### Community 17 - "Projects/Index.vue"
Cohesion: 0.16
Nodes (9): applyFilters(), currentKind, filterKind(), hasActiveFilters, props, resetFilters(), searchQuery, selectedLifecycle (+1 more)

### Community 18 - "Reports/Index.vue"
Cohesion: 0.13
Nodes (7): csvExportUrl, excelExportUrl, filterForm, props, recipientInput, showModal, subForm

### Community 19 - ".parent"
Cohesion: 0.06
Nodes (8): RollupDaily, StatusDef, Location, HandleInertiaRequests, Inertia\Middleware, AnalyticsRollupTest, FreeWifiImportTest, StatusWorkflowTest

### Community 20 - "Edit.vue"
Cohesion: 0.15
Nodes (6): activeTab, fieldForm, fieldOptionsString, metaForm, props, statusForm

### Community 21 - "Sites/Create.vue"
Cohesion: 0.15
Nodes (8): currentProjectFields, currentProjectStatuses, filteredMunicipalities, form, props, selectedMunCode, selectedProject, selectedProvinceCode

### Community 22 - "Submissions/Create.vue"
Cohesion: 0.15
Nodes (8): availableInterventions, availableStatuses, customFields, form, gpsStatusMessage, isCapturingGps, props, selectedSite

### Community 23 - "ProjectType"
Cohesion: 0.14
Nodes (5): User, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Fortify\TwoFactorAuthenticatable, AuthTest

### Community 24 - "Geo/Index.vue"
Cohesion: 0.20
Nodes (9): activeSelection, expandedProvinces, lguBarangays, loadingBarangays, props, selectEntity(), selectLgu(), showingBarangaysFor (+1 more)

### Community 25 - "Users/Index.vue"
Cohesion: 0.17
Nodes (5): createForm, grantForm, props, selectedUserForGrant, showCreateModal

### Community 26 - "Institutions/Index.vue"
Cohesion: 0.20
Nodes (8): applyFilters(), currentKind, filterKind(), instForm, props, resetFilters(), searchQuery, showModal

### Community 27 - "ReportController.php"
Cohesion: 0.36
Nodes (3): ExcelReportGenerator, ReportController, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 28 - "composer.json"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, license, minimum-stability, name, prefer-stable, Tests\\ (+2 more)

### Community 29 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, pestphp/pest (+2 more)

### Community 30 - "UpdateRollupJob.php"
Cohesion: 0.36
Nodes (6): UpdateRollupJob, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels

### Community 31 - "StatusDef"
Cohesion: 0.20
Nodes (5): GrantResolver, EnsureTwoFactorEnforced, Closure, Symfony\Component\HttpFoundation\Response, GrantResolverTest

### Community 32 - "StatusWorkflow"
Cohesion: 0.19
Nodes (7): DispatchReportsCommand, Carbon, DistrictsImport, ImportFreeWifiData, RebuildRollupCommand, Command, Illuminate\Console\Command

### Community 33 - "require"
Cohesion: 0.25
Nodes (8): require, inertiajs/inertia-laravel, laravel/fortify, laravel/framework, laravel/tinker, openspout/openspout, php, tightenco/ziggy

### Community 34 - "Dashboard.vue"
Cohesion: 0.22
Nodes (3): activeTab, currentTime, props

### Community 35 - "ReviewQueue.vue"
Cohesion: 0.25
Nodes (3): currentStatus, props, reviewNotesMap

### Community 36 - "RollupDaily"
Cohesion: 0.25
Nodes (4): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, ExampleTest, TestCase

### Community 37 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 40 - "AppShell.vue"
Cohesion: 0.25
Nodes (6): currentRoute, currentTheme, isAdminOpen, isMobileMenuOpen, page, searchQuery

### Community 41 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 42 - "extra"
Cohesion: 0.40
Nodes (5): dev-master, extra, branch-alias, laravel, dont-discover

### Community 48 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 79 - "REGION II SITE STATUS 2026_185a78b2.md"
Cohesion: 0.15
Nodes (12): Sheet: AP MAC  ADDRESS, Sheet: Copy of II, Sheet: NEW SITES ELEARNING, Sheet: NEW SITES FOR DECLARATION, Sheet: NEW SITES PIALEOS - RPS (DATA L, Sheet: NEW SITES (PICS MUN LOT 2), Sheet: New Sites (POP Extension 2025), Sheet: October (+4 more)

### Community 80 - "DICT Region 2 Infrastructure & Program Rollout Monitoring Platform"
Cohesion: 0.25
Nodes (8): 4 Tracking Archetypes (Project Kinds), DICT Region 2 Infrastructure & Program Rollout Monitoring Platform, Implementation Details, Implementation Status Matrix (100% COMPLETE), Master Implementation Blueprint (`Plan.md`), Objective, Plan#10: Real Operational Data Ingestion & Enterprise Operationalization, Project Architecture & Environment Overview

### Community 83 - "Plan#8: Rollup Tables, Backlog Tracking & Analytics Engine"
Cohesion: 0.40
Nodes (5): Background Jobs & Calculations, Database Schema, Objective, Plan#8: Rollup Tables, Backlog Tracking & Analytics Engine, Verification & Acceptance Criteria

### Community 84 - "Plan#6: Field Reporting, Timeline & Verification Engine"
Cohesion: 0.40
Nodes (5): Core Features, Database Schema, Objective, Plan#6: Field Reporting, Timeline & Verification Engine, Verification & Acceptance Criteria

### Community 85 - "Plan#2: Authentication, Scoped RBAC & 2FA Engine"
Cohesion: 0.40
Nodes (5): Database Schema, Logic & Services, Objective, Plan#2: Authentication, Scoped RBAC & 2FA Engine, Verification & Acceptance Criteria

### Community 86 - "Plan#3: PSGC Geographic Hierarchy, Districts & Boundary Layers"
Cohesion: 0.40
Nodes (5): Database Schema, Objective, Plan#3: PSGC Geographic Hierarchy, Districts & Boundary Layers, Processing & CLI Pipelines, Verification & Acceptance Criteria

### Community 87 - "Plan#4: Configurable Multi-Project & Portfolio Engine"
Cohesion: 0.40
Nodes (5): Database Schema, Features & UI, Objective, Plan#4: Configurable Multi-Project & Portfolio Engine, Verification & Acceptance Criteria

### Community 88 - "Plan#5: Shared Location & Institution Registries"
Cohesion: 0.40
Nodes (5): Database Schema, Logic & Features, Objective, Plan#5: Shared Location & Institution Registries, Verification & Acceptance Criteria

### Community 89 - "Project Completion & Production Handover Summary"
Cohesion: 0.50
Nodes (4): 🏆 100% Phase Completion Verification, 🔑 Pre-Seeded Verification & Demo Accounts, ⚙️ Production Operations Reference, Project Completion & Production Handover Summary

### Community 90 - "Plan#7: Interactive Cartographic Map Engine (Leaflet + Supercluster)"
Cohesion: 0.50
Nodes (4): Frontend Components (`resources/js/Components/Map/`), Objective, Plan#7: Interactive Cartographic Map Engine (Leaflet + Supercluster), Verification & Acceptance Criteria

### Community 91 - "Plan#9: Reporting, Audit Trail & Automated Dispatch"
Cohesion: 0.50
Nodes (4): Implementation Details, Objective, Plan#9: Reporting, Audit Trail & Automated Dispatch, Verification & Acceptance Criteria

### Community 92 - "Plan#1: Foundational Design System & App Shell"
Cohesion: 0.50
Nodes (4): Objective, Plan#1: Foundational Design System & App Shell, Scope & Tasks, Verification & Acceptance Criteria

## Knowledge Gaps
- **241 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+236 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `ProjectType` to `StatusWorkflow`, `Illuminate\Http\Request`, `User`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `RollupDaily`, `BoundaryValidator`, `Intervention`, `ReportingAuditTest`, `SiteRegistryTest`, `InteractiveMapTest`, `ProjectCrudTest`, `UpdateUserProfileInformation.php`, `.parent`, `GeoTest`, `StatusDef`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Why does `ProjectSite` connect `KpiCalculator` to `StatusWorkflow`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Http\Request`, `BoundaryValidator`, `RollupDaily`, `Intervention`, `ReportingAuditTest`, `ProjectSite`, `InteractiveMapTest`, `.parent`, `ReportController.php`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Why does `ProjectType` connect `Illuminate\Http\Request` to `StatusWorkflow`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `BoundaryValidator`, `RollupDaily`, `ReportingAuditTest`, `SiteRegistryTest`, `InteractiveMapTest`, `ProjectCrudTest`, `.parent`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Are the 15 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 15 INFERRED edges - model-reasoned connections that need verification._
- **Are the 24 inferred relationships involving `ProjectSite` (e.g. with `.handle()` and `.calculateAverageCycleTime()`) actually correct?**
  _`ProjectSite` has 24 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _241 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Relations\BelongsTo` be split into smaller, more focused modules?**
  _Cohesion score 0.05357142857142857 - nodes in this community are weakly interconnected._