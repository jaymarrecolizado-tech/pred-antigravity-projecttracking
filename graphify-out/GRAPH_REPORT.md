# Graph Report - .  (2026-10-15)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 861 nodes · 1749 edges · 79 communities (64 shown, 15 thin omitted)
- Extraction: 90% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 169 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `26c27bab`
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

## Communities (79 total, 15 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.06
Nodes (25): RoleGrant, AuditLog, FieldDef, IndicatorTarget, InterventionType, Program, ProjectScope, Institution (+17 more)

### Community 1 - "Illuminate\Http\Request"
Cohesion: 0.06
Nodes (23): GrantResolver, Project, Location, SiteImportService, AuditController, GeoController, UserController, TwoFactorSetupController (+15 more)

### Community 2 - "User"
Cohesion: 0.05
Nodes (19): CreateNewUser, ResetUserPassword, UpdateUserPassword, UpdateUserProfileInformation, User, AppServiceProvider, FortifyServiceProvider, Illuminate\Foundation\Auth\User (+11 more)

### Community 3 - "devDependencies"
Cohesion: 0.06
Nodes (33): axios, concurrently, @inertiajs/vue3, laravel-vite-plugin, leaflet, mapshaper, allowScripts, esbuild@0.25.12 (+25 more)

### Community 4 - "BoundaryValidator"
Cohesion: 0.09
Nodes (5): BoundaryValidator, Attachment, FraudDetector, BoundaryValidatorTest, FraudDetectorTest

### Community 5 - "PsgcArea"
Cohesion: 0.11
Nodes (9): DistrictsImport, ImportFreeWifiData, PsgcImport, RebuildRollupCommand, DistrictMembership, PsgcArea, Command, Illuminate\Console\Command (+1 more)

### Community 6 - "Intervention"
Cohesion: 0.11
Nodes (6): Intervention, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Relations\HasOne, static, FieldSubmissionTest

### Community 7 - "ReportingAuditTest"
Cohesion: 0.16
Nodes (5): DispatchReportsCommand, Carbon, AuditLogger, ReportSubscription, ReportingAuditTest

### Community 8 - "ProjectSite"
Cohesion: 0.13
Nodes (3): HasMany, ProjectSite, AnalyticsRollupTest

### Community 9 - "MapView.vue"
Cohesion: 0.24
Nodes (15): createClusterIcon(), createSiteIcon(), getPercentageColor(), interpolateHex(), activeMetricLabel, emit, filteredFeatures, initMap() (+7 more)

### Community 10 - "Map/Index.vue"
Cohesion: 0.12
Nodes (9): activePointsCount, activeSelection, clusterMode, granularitySlider, props, selectedBasemap, selectedMetric, selectedProjectIds (+1 more)

### Community 11 - "Projects/Create.vue"
Cohesion: 0.13
Nodes (8): currentStep, expandedProvinces, form, generateCode(), getMunicipalitiesForProvince(), onProgramChange(), props, toggleProvince()

### Community 13 - "scripts"
Cohesion: 0.13
Nodes (15): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+7 more)

### Community 14 - "Sites/Index.vue"
Cohesion: 0.14
Nodes (12): applyFilters(), hasActiveFilters, importPayloadString, importProjectId, isImporting, props, resetFilters(), searchQuery (+4 more)

### Community 15 - "KpiCalculator"
Cohesion: 0.29
Nodes (4): KpiCalculator, Carbon, DashboardController, Carbon\Carbon

### Community 16 - "TwoFactorSetup.vue"
Cohesion: 0.14
Nodes (8): confirmCode, confirmError, confirming, loading, props, qrCodeSvg, recoveryCodes, secretKey

### Community 17 - "Projects/Index.vue"
Cohesion: 0.16
Nodes (9): applyFilters(), currentKind, filterKind(), hasActiveFilters, props, resetFilters(), searchQuery, selectedLifecycle (+1 more)

### Community 18 - "Reports/Index.vue"
Cohesion: 0.14
Nodes (6): excelExportUrl, filterForm, props, recipientInput, showModal, subForm

### Community 19 - ".parent"
Cohesion: 0.18
Nodes (3): HandleInertiaRequests, Inertia\Middleware, FreeWifiImportTest

### Community 20 - "Edit.vue"
Cohesion: 0.15
Nodes (6): activeTab, fieldForm, fieldOptionsString, metaForm, props, statusForm

### Community 21 - "Sites/Create.vue"
Cohesion: 0.15
Nodes (8): currentProjectFields, currentProjectStatuses, filteredMunicipalities, form, props, selectedMunCode, selectedProject, selectedProvinceCode

### Community 22 - "Submissions/Create.vue"
Cohesion: 0.15
Nodes (8): availableInterventions, availableStatuses, customFields, form, gpsStatusMessage, isCapturingGps, props, selectedSite

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
Cohesion: 0.25
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

### Community 33 - "require"
Cohesion: 0.25
Nodes (8): require, inertiajs/inertia-laravel, laravel/fortify, laravel/framework, laravel/tinker, openspout/openspout, php, tightenco/ziggy

### Community 35 - "ReviewQueue.vue"
Cohesion: 0.25
Nodes (3): currentStatus, props, reviewNotesMap

### Community 37 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 40 - "AppShell.vue"
Cohesion: 0.33
Nodes (4): currentRoute, currentTheme, page, searchQuery

### Community 41 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 42 - "extra"
Cohesion: 0.40
Nodes (5): dev-master, extra, branch-alias, laravel, dont-discover

### Community 48 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

## Knowledge Gaps
- **163 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+158 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **15 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Http\Request`, `BoundaryValidator`, `PsgcArea`, `Intervention`, `ReportingAuditTest`, `ProjectSite`, `SiteRegistryTest`, `InteractiveMapTest`, `.parent`, `ReportController.php`?**
  _High betweenness centrality (0.065) - this node is a cross-community bridge._
- **Why does `ProjectSite` connect `ProjectSite` to `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Http\Request`, `BoundaryValidator`, `PsgcArea`, `RollupDaily`, `Intervention`, `ReportingAuditTest`, `InteractiveMapTest`, `KpiCalculator`, `.parent`, `ReportController.php`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `ProjectType` connect `ProjectType` to `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Http\Request`, `StatusWorkflow`, `User`, `BoundaryValidator`, `PsgcArea`, `Intervention`, `SiteRegistryTest`, `ProjectSite`, `Illuminate\Database\Eloquent\Relations\HasMany`, `InteractiveMapTest`, `.parent`, `StatusDef`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Are the 15 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 15 INFERRED edges - model-reasoned connections that need verification._
- **Are the 24 inferred relationships involving `ProjectSite` (e.g. with `.handle()` and `.calculateAverageCycleTime()`) actually correct?**
  _`ProjectSite` has 24 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _163 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Relations\BelongsTo` be split into smaller, more focused modules?**
  _Cohesion score 0.05867082035306334 - nodes in this community are weakly interconnected._