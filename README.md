# RECON • Regional Execution, Coordination & Operations Network
### Department of Information and Communications Technology — Regional Office 02 (Cagayan Valley)

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3.x-4FC08D?style=flat&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v2-9553E9?style=flat&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Vanilla CSS](https://img.shields.io/badge/CSS-Vanilla%20Tokens-1572B6?style=flat&logo=css3&logoColor=white)](resources/css)
[![Leaflet](https://img.shields.io/badge/Map-Leaflet%20%2B%20Esri-199900?style=flat&logo=leaflet&logoColor=white)](https://leafletjs.com)
[![Graphify](https://img.shields.io/badge/Knowledge%20Graph-Graphify-6366F1?style=flat)](graphify-out)
[![Test Suite](https://img.shields.io/badge/Tests-59%20Passing%20(371%20Assertions)-success)](tests)

> **RECON Mission**: A mission-critical geospatial command, monitoring, and portfolio execution platform engineered for the **Department of Information and Communications Technology (DICT) Regional Office 02 (Cagayan Valley)**. Tracks initiatives across 5 provinces (**Batanes**, **Cagayan**, **Isabela**, **Nueva Vizcaya**, **Quirino**), 12 Congressional Districts, and 93 Cities & Municipalities with real-time analytics, mobile field verification, GPS proximity safeguards, and tamper-evident audit trails.

---

## 🏛️ Master Blueprint & Implementation Status

All 10 implementation phases defined in [`Plan.md`](Plan.md) are **100% complete**:

| Phase | Description | Status |
| :--- | :--- | :---: |
| **Plan#1** | Foundational Design System & App Shell (Vanilla CSS Tokens, NMS-V2 Theme, Light/Dark) | `COMPLETE` |
| **Plan#2** | Standalone Fortify Auth & Scoped RBAC (PSGC Grants, TOTP 2FA Enforcement) | `COMPLETE` |
| **Plan#3** | PSGC Administrative Hierarchy & Congressional Districts (694 Entities, GeoJSON Layers) | `COMPLETE` |
| **Plan#4** | Multi-Project & Portfolio Engine (4 Tracking Archetypes, State Machine Workflows) | `COMPLETE` |
| **Plan#5** | Shared Location & Institution Registries (Ray-Casting Point-in-Polygon, <50m Deduplication) | `COMPLETE` |
| **Plan#6** | Field Reporting, Timeline & Verification (Append-Only Interventions, GPS Proximity, SHA-256) | `COMPLETE` |
| **Plan#7** | Interactive Cartographic Map Engine (Leaflet, CARTO Positron/Dark, Supercluster, Slide Tray) | `COMPLETE` |
| **Plan#8** | Rollup Tables, Backlog Tracking & Analytics (Asynchronous Aggregation, KPI Engine, ECharts) | `COMPLETE` |
| **Plan#9** | Reporting, Audit Trail & Dispatch (OpenSpout Excel, PDF Dossiers, Subscriptions, Audit Log) | `COMPLETE` |
| **Plan#10** | Real Free Wi-Fi Dataset Ingestion (2,095 Records -> 1,868 Sites, 996 Physical Locations) | `COMPLETE` |

---

## 🚀 Key Features

- **4 Tracking Archetypes**:
  - **Kind A (Site Deployment)**: Point-based hardware installations (Free Wi-Fi, GovNet, PIALEOS, Tech4ED).
  - **Kind B (Institution Adoption)**: Agency & school software rollouts (eLGU, eGovPH, GovMail, PNPKI).
  - **Kind C (Activities & Beneficiaries)**: Training headcounts & equipment distributions with GAD disaggregation.
  - **Kind D (Milestones & Studies)**: Survey sweep coverage (NICTHS, NICTEF).
- **Interactive Cartographic Dashboard**:
  - CARTO Positron Light & Dark Matter vector-matched basemaps.
  - Dual clustering: Distance-based Supercluster with dynamic **Merge <---> Detail** slider and administrative rollup.
  - Percentage-colored bubble pins (Crimson ≤20% $\to$ Amber ~50% $\to$ Emerald Green ≥95%).
  - Multi-project slide-over filter tray and interactive site dossier drawer.
- **Strict Anti-Fraud & Append-Only Audit Integrity**:
  - `Intervention` and `AuditLog` database models implement `LogicException` prevention against updates and deletes.
  - Mobile GPS coordinate capture with proximity threshold verification (<500m) and photo SHA-256 deduplication.
- **Enterprise Reporting & Streaming Exports**:
  - High-throughput Excel exports streaming tens of thousands of rows with minimal memory via OpenSpout 4.x.
  - Print-ready executive PDF dossiers with embedded map summaries and KPI breakdowns.
  - Automated recurring report subscriptions delivered via Laravel Console Schedule (`php artisan reports:dispatch`).

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12.x, PHP 8.2+
- **Frontend**: Vue 3 (Composition API), Inertia.js v2
- **Styling**: Strictly **Vanilla CSS** with design tokens (`resources/css/tokens.css`, `base.css`, `components.css`)
- **Geospatial & Mapping**: Leaflet 1.9.4, CARTO Basemaps, Supercluster, GeoJSON Boundary Layers
- **Database**: MariaDB 10.4+ / MySQL 8.0+ compatible (indexed decimal lat/lng coordinates)
- **Data Streaming**: OpenSpout 4.x (Excel XLSX read/write)
- **Charts & Telemetry**: Apache ECharts 5.x

---

## ⚙️ Local Setup & Quickstart

### Prerequisites
- PHP 8.2 or higher with `pdo_mysql`, `gd`, `zip`, `xml`, `mbstring` extensions
- Composer 2.x
- Node.js 18.x or 20.x
- MySQL or MariaDB (e.g. XAMPP)

### Installation
```bash
# Clone the repository
git clone <repo-url> pred-project-tracking
cd pred-project-tracking

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database credentials in .env, then migrate and seed:
php artisan migrate --seed

# Ingest real Free Wi-Fi 2026 dataset (1,868 sites across Region 2)
php artisan wifi:import

# Build frontend assets
npm run build

# Start local server
php artisan serve --port=8000
```

---

## 🔑 Demo & Verification Accounts

All pre-configured testing accounts use password `Password123!` (except Super Admin as noted):

| Role | Email | Password | Access Scope / 2FA Status |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@dict.gov.ph` | `AdminSecret123!` | System-wide authority; TOTP 2FA enabled |
| **Project Manager** | `pm.nbp@dict.gov.ph` | `Password123!` | Manages Free Wi-Fi & GovNet; TOTP 2FA enabled |
| **Reviewer / QA** | `reviewer.r02@dict.gov.ph` | `Password123!` | Region-wide QA verification queue; TOTP 2FA enabled |
| **Provincial Encoder** | `encoder.tuguegarao@dict.gov.ph` | `Password123!` | Scoped to Cagayan Province (`0215`); direct access |
| **Partner / LGU Viewer** | `partner.ilagan@dict.gov.ph` | `Password123!` | Scoped to Isabela Province (`0231`); read-only |

---

## 🧪 Automated Test Suite

Run the full automated test suite covering all 10 implementation plans:

```bash
php artisan test
```

Expected output:
```
Tests:    59 passed (371 assertions)
Duration: ~7-10s
```

---

## 📖 Documentation & Guides

- **Master Implementation Blueprint**: [`Plan.md`](Plan.md)
- **Production VPS Hosting & CloudPanel Guide**: [`DEPLOYMENT.md`](DEPLOYMENT.md)
- **Operational Data Ingestion Walkthrough**: Walkthrough documentation in `.gemini/brain` artifacts.

---

## 🔒 Security & Compliance

- **Append-Only Ledger**: Database triggers and model event listeners prevent tampering with field logs or audit rows.
- **Mandatory 2FA**: `EnsureTwoFactorEnforced` middleware redirects privileged roles to `/two-factor/setup` before dashboard access.
- **PSGC Spatial Scoping**: Encoders can only access and submit reports within their explicitly assigned geographic bounds.
