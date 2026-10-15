<template>
    <AppShell>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-group">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <span class="badge badge-kind-a" style="font-size: 0.68rem; font-weight: 700;">DICT REGION 02</span>
                    <span style="font-size: 0.76rem; font-family: var(--font-mono); color: var(--c-ink-muted);">FREE WI-FI FOR ALL</span>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--c-ink); margin: 0;">
                    Free Wi-Fi Reports & Data Exports
                </h1>
                <div class="page-subtitle">
                    Official DICT Regional Office 02 Free Wi-Fi accomplishment matrices, OpenSpout streaming exports, and scheduled digests.
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="page-actions" style="display: flex; gap: 0.65rem; flex-wrap: wrap; align-items: center;">
                <button type="button" @click="openSubscriptionModal" class="btn btn-secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>Schedule Digest</span>
                </button>
                <button type="button" @click="printDossier" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>Print Dossier</span>
                </button>
                <div style="display: flex; gap: 0.4rem;">
                    <a :href="excelExportUrl" class="btn btn-primary" title="Export filtered records in Excel format">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export Excel (.xlsx)</span>
                    </a>
                    <a :href="csvExportUrl" class="btn btn-secondary" title="Export filtered records in CSV format">
                        <span>CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 1-Click Instant Export Preset Cards -->
        <div style="margin-bottom: 1.5rem;">
            <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--c-ink-muted); margin-bottom: 0.65rem;">
                ⚡ 1-Click Official Export Presets
            </div>
            <div class="grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                <!-- Preset 1: Full Registry -->
                <div class="card card-interactive" style="padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <span class="badge badge-kind-a">Full Dataset</span>
                            <span style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--c-ink-muted);">1,868 Sites</span>
                        </div>
                        <h3 style="font-size: 0.96rem; font-weight: 700; color: var(--c-ink); margin: 0 0 0.25rem 0;">
                            Complete Free Wi-Fi Registry
                        </h3>
                        <p style="font-size: 0.76rem; color: var(--c-ink-secondary); margin: 0 0 1rem 0; line-height: 1.4;">
                            All 1,868 sites across Batanes, Cagayan, Isabela, Nueva Vizcaya, and Quirino with GPS & PSGC codes.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="/reports/export/excel" class="btn btn-primary btn-sm" style="flex: 1;">
                            Download .xlsx
                        </a>
                        <a href="/reports/export/csv" class="btn btn-secondary btn-sm">
                            .csv
                        </a>
                    </div>
                </div>

                <!-- Preset 2: Operational Sites -->
                <div class="card card-interactive" style="padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <span class="badge badge-success">Online Only</span>
                            <span style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--scale-success); font-weight: 600;">Active Hotspots</span>
                        </div>
                        <h3 style="font-size: 0.96rem; font-weight: 700; color: var(--c-ink); margin: 0 0 0.25rem 0;">
                            Operational Wi-Fi Hotspots
                        </h3>
                        <p style="font-size: 0.76rem; color: var(--c-ink-secondary); margin: 0 0 1rem 0; line-height: 1.4;">
                            Verified live public internet access sites in public plazas, municipal halls, and SUC campuses.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="/reports/export/excel?status=operational" class="btn btn-primary btn-sm" style="flex: 1;">
                            Download .xlsx
                        </a>
                        <a href="/reports/export/csv?status=operational" class="btn btn-secondary btn-sm">
                            .csv
                        </a>
                    </div>
                </div>

                <!-- Preset 3: Backlog / Overdue -->
                <div class="card card-interactive" style="padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <span class="badge badge-danger">Needs Action</span>
                            <span style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--scale-danger); font-weight: 600;">Overdue Targets</span>
                        </div>
                        <h3 style="font-size: 0.96rem; font-weight: 700; color: var(--c-ink); margin: 0 0 0.25rem 0;">
                            Delinquent & Backlog Sites
                        </h3>
                        <p style="font-size: 0.76rem; color: var(--c-ink-secondary); margin: 0 0 1rem 0; line-height: 1.4;">
                            Target sites requiring field inspection, contract extension, or power link re-activation.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="/reports/export/excel?status=planned" class="btn btn-primary btn-sm" style="flex: 1; background: var(--scale-danger); border-color: var(--scale-danger);">
                            Download .xlsx
                        </a>
                        <a href="/reports/export/csv?status=planned" class="btn btn-secondary btn-sm">
                            .csv
                        </a>
                    </div>
                </div>

                <!-- Preset 4: Official Printable Executive Dossier -->
                <div class="card card-interactive" style="padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <span class="badge badge-neutral">Print-Ready</span>
                            <span style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--c-ink-muted);">DICT R02 Seal</span>
                        </div>
                        <h3 style="font-size: 0.96rem; font-weight: 700; color: var(--c-ink); margin: 0 0 0.25rem 0;">
                            Executive Briefing Dossier
                        </h3>
                        <p style="font-size: 0.76rem; color: var(--c-ink-secondary); margin: 0 0 1rem 0; line-height: 1.4;">
                            Formatted printable summary for regional directors, provincial heads, and executive audits.
                        </p>
                    </div>
                    <button type="button" @click="printDossier" class="btn btn-outline btn-sm" style="width: 100%;">
                        Print Executive Dossier
                    </button>
                </div>
            </div>
        </div>

        <!-- Printable Executive Header (Visible only when Printing) -->
        <div class="print-only-header" style="display: none;">
            <div style="text-align: center; margin-bottom: 1.5rem; border-bottom: 2px solid #000; padding-bottom: 1rem;">
                <h2 style="font-size: 1.1rem; margin: 0; text-transform: uppercase;">Republic of the Philippines</h2>
                <h1 style="font-size: 1.35rem; margin: 0.25rem 0; font-weight: 800;">DEPARTMENT OF INFORMATION AND COMMUNICATIONS TECHNOLOGY</h1>
                <div style="font-size: 0.95rem; font-weight: 700;">Regional Office 02 — Cagayan Valley</div>
                <div style="font-size: 0.85rem; margin-top: 0.35rem; color: #444;">FREE WI-FI FOR ALL PROJECT • REGIONAL ACCOMPLISHMENT DOSSIER</div>
            </div>
        </div>

        <!-- Custom Filter Controls Card -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <div style="font-weight: 700; font-size: 0.9rem; color: var(--c-ink); margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <span>Accomplishment & Geographic Query Filter</span>
                    <span class="badge badge-primary" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                        📍 {{ activeGeoScope }}
                    </span>
                </div>
                <span style="font-size: 0.74rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                    {{ matrix.total_sites }} Sites in Filtered Scope
                </span>
            </div>

            <!-- Tier 1: 4-Level Geographic Hierarchy Filter -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
                <div>
                    <label class="form-label" style="font-size: 0.75rem;">1. Province</label>
                    <select v-model="filterForm.province_prefix" @change="onProvinceChange" class="form-input">
                        <option v-for="prov in provinces" :key="prov.prefix" :value="prov.prefix">
                            {{ prov.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.75rem;">2. Congressional District</label>
                    <select v-model="filterForm.district" @change="onDistrictChange" class="form-input">
                        <option value="">All Congressional Districts (12)</option>
                        <option v-for="dist in filteredDistricts" :key="dist.name" :value="dist.name">
                            {{ dist.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.75rem;">3. Municipality / City</label>
                    <select v-model="filterForm.municipality" @change="onMunicipalityChange" class="form-input">
                        <option value="">All Municipalities / Cities ({{ filteredMunicipalities.length }})</option>
                        <option v-for="mun in filteredMunicipalities" :key="mun.name + mun.province" :value="mun.name">
                            {{ mun.name }} ({{ mun.province }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.75rem;">4. Barangay</label>
                    <select v-model="filterForm.barangay" @change="applyFilters" class="form-input">
                        <option value="">All Barangays ({{ filteredBarangays.length }})</option>
                        <option v-for="bgy in filteredBarangays" :key="bgy.name + bgy.municipality" :value="bgy.name">
                            {{ bgy.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tier 2: Contract Batch, Status & Action Buttons -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; align-items: flex-end; padding-top: 0.75rem; border-top: 1px dashed var(--c-border);">
                <div>
                    <label class="form-label" style="font-size: 0.75rem;">Delivery Lot / Contract Batch</label>
                    <select v-model="filterForm.project_id" @change="applyFilters" class="form-input">
                        <option value="">All Delivery Lots (14)</option>
                        <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                            {{ proj.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.75rem;">Wi-Fi Status</label>
                    <select v-model="filterForm.status" @change="applyFilters" class="form-input">
                        <option value="">All Statuses</option>
                        <option v-for="st in statuses" :key="st" :value="st">
                            {{ formatStatusLabel(st) }}
                        </option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" @click="resetFilters" class="btn btn-ghost" style="flex: 1;">
                        Reset Filters
                    </button>
                    <a :href="excelExportUrl" class="btn btn-primary" style="flex: 1;">
                        Download Scope
                    </a>
                </div>
            </div>
        </div>

        <!-- 4-Stat Filtered KPI Summary Ribbon -->
        <div class="grid-4" style="margin-bottom: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Sites in Selection</span>
                    <span class="badge badge-neutral">Filtered Scope</span>
                </div>
                <div class="metric-value">{{ matrix.total_sites }}</div>
                <div class="metric-subtext">Free Wi-Fi monitored sites</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Operational Hotspots</span>
                    <span class="badge badge-success">Online</span>
                </div>
                <div class="metric-value">{{ matrix.operational_sites }}</div>
                <div class="metric-subtext">Providing active public internet</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Accomplishment Rate</span>
                    <span style="color: var(--scale-success); font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem;">
                        {{ matrix.accomplishment_rate }}%
                    </span>
                </div>
                <div class="metric-value">{{ matrix.accomplishment_rate }}%</div>
                <div class="metric-subtext">Activation target reached</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Active Subscriptions</span>
                    <span class="badge badge-primary">{{ subscriptions.length }} Scheduled</span>
                </div>
                <div class="metric-value">{{ subscriptions.length }}</div>
                <div class="metric-subtext">Automated email dispatches</div>
            </div>
        </div>

        <!-- Preview Matrix Table with Touch Scroll Wrapper -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <div>
                    <div class="card-title">Free Wi-Fi Sites Accomplishment Matrix</div>
                    <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                        Showing top {{ matrix.preview_rows.length }} of {{ matrix.total_sites }} matching sites for scope: <strong>{{ activeGeoScope }}</strong>
                    </div>
                </div>
                <div style="display: flex; gap: 0.4rem;">
                    <a :href="excelExportUrl" class="btn btn-outline btn-sm">Export Excel</a>
                    <a :href="csvExportUrl" class="btn btn-secondary btn-sm">Export CSV</a>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Site Code</th>
                            <th>Delivery Lot</th>
                            <th>Facility Name</th>
                            <th>Province</th>
                            <th>Congressional District</th>
                            <th>Municipality</th>
                            <th>Barangay</th>
                            <th>Target Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="matrix.preview_rows.length === 0">
                            <td colspan="10" style="text-align: center; padding: 2rem; color: var(--c-ink-muted);">
                                No site records match the specified query filters.
                            </td>
                        </tr>
                        <tr v-for="row in matrix.preview_rows" :key="row.id">
                            <td style="font-family: var(--font-mono); font-weight: 700; color: var(--c-ink);">
                                {{ row.site_code }}
                            </td>
                            <td>{{ row.project_name }}</td>
                            <td style="font-weight: 600;">{{ row.facility_name }}</td>
                            <td>{{ row.province }}</td>
                            <td style="font-size: 0.8rem; color: var(--c-primary); font-weight: 600;">
                                {{ row.district }}
                            </td>
                            <td>{{ row.municipality }}</td>
                            <td style="font-size: 0.82rem; color: var(--c-ink-muted);">
                                {{ row.barangay }}
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 0.78rem;">{{ row.target_date }}</td>
                            <td>
                                <span class="chip-status" :class="row.status === 'operational' ? 'status-online' : 'status-ongoing'">
                                    <span class="status-dot"></span>
                                    <span>{{ formatStatusLabel(row.status) }}</span>
                                </span>
                            </td>
                            <td>
                                <Link :href="'/sites/' + row.id" class="btn btn-ghost btn-sm">Dossier →</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Scheduled Automated Report Subscriptions Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Automated Scheduled Report Subscriptions</div>
                    <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                        Recurring executive digests automatically compiled and dispatched via console cron
                    </div>
                </div>
                <button type="button" @click="openSubscriptionModal" class="btn btn-secondary btn-sm">
                    + New Subscription
                </button>
            </div>
            <div class="card-body">
                <div v-if="subscriptions.length === 0" style="padding: 1.5rem; text-align: center; color: var(--c-ink-muted);">
                    You have no active automated report subscriptions. Click "Schedule Digest" above to configure periodic email delivery.
                </div>
                <div v-else style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div v-for="sub in subscriptions" :key="sub.id"
                         style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--c-border); border-radius: var(--radius-md); flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div style="font-weight: 700; color: var(--c-ink); font-size: 0.92rem;">
                                {{ sub.title }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                                Frequency: <strong style="text-transform: capitalize; color: var(--c-primary);">{{ sub.frequency }}</strong> •
                                Format: <span style="font-family: var(--font-mono); text-transform: uppercase;">{{ sub.format }}</span> •
                                Recipients: {{ sub.recipients.join(', ') }}
                            </div>
                        </div>
                        <button type="button" @click="cancelSubscription(sub.id)" class="btn btn-outline btn-sm" style="color: var(--scale-danger); border-color: rgba(239, 68, 68, 0.3);">
                            Cancel Subscription
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Configuration Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
            <div class="modal-card">
                <div class="modal-header">
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--c-ink);">Schedule Report Subscription</h3>
                    <button type="button" @click="showModal = false" class="btn btn-ghost btn-sm">✕</button>
                </div>
                <form @submit.prevent="submitSubscription" style="padding: 1.25rem;">
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Subscription Title</label>
                        <input type="text" v-model="subForm.title" class="form-input" required />
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label">Frequency</label>
                            <select v-model="subForm.frequency" class="form-input">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly (Mondays)</option>
                                <option value="monthly">Monthly (1st day)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Format</label>
                            <select v-model="subForm.format" class="form-input">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="pdf">PDF Dossier</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <label class="form-label">Recipient Email Addresses (Comma Separated)</label>
                        <input type="text" v-model="recipientInput" class="form-input" placeholder="e.g. director@dict.gov.ph, planner@dict.gov.ph" required />
                        <div style="font-size: 0.72rem; color: var(--c-ink-muted); margin-top: 0.25rem;">
                            Current active query filters will be attached to this subscription.
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" @click="showModal = false" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="subForm.processing">
                            Confirm Subscription
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
    provinces: {
        type: Array,
        required: true,
    },
    districts: {
        type: Array,
        default: () => [],
    },
    municipalities: {
        type: Array,
        default: () => [],
    },
    barangays: {
        type: Array,
        default: () => [],
    },
    projects: {
        type: Array,
        required: true,
    },
    programs: {
        type: Array,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    matrix: {
        type: Object,
        required: true,
    },
    subscriptions: {
        type: Array,
        default: () => [],
    },
});

const filterForm = ref({
    project_id: props.filters.project_id || '',
    province_prefix: props.filters.province_prefix || '',
    district: props.filters.district || '',
    municipality: props.filters.municipality || '',
    barangay: props.filters.barangay || '',
    status: props.filters.status || '',
});

// Cascading Filter Logic
const filteredDistricts = computed(() => {
    if (!filterForm.value.province_prefix) return props.districts;
    return props.districts.filter(d => d.province_prefix === filterForm.value.province_prefix);
});

const filteredMunicipalities = computed(() => {
    let list = props.municipalities;
    if (filterForm.value.province_prefix) {
        list = list.filter(m => m.province_prefix === filterForm.value.province_prefix);
    }
    if (filterForm.value.district) {
        list = list.filter(m => m.district === filterForm.value.district);
    }
    return list;
});

const filteredBarangays = computed(() => {
    if (filterForm.value.municipality) {
        const munLower = filterForm.value.municipality.toLowerCase();
        return props.barangays.filter(b => b.municipality.toLowerCase() === munLower);
    }
    if (filterForm.value.province_prefix) {
        const provObj = props.provinces.find(p => p.prefix === filterForm.value.province_prefix);
        if (provObj) {
            const pName = provObj.name.toLowerCase();
            return props.barangays.filter(b => b.province.toLowerCase() === pName);
        }
    }
    return props.barangays.slice(0, 100);
});

function onProvinceChange() {
    filterForm.value.district = '';
    filterForm.value.municipality = '';
    filterForm.value.barangay = '';
    applyFilters();
}

function onDistrictChange() {
    filterForm.value.municipality = '';
    filterForm.value.barangay = '';
    applyFilters();
}

function onMunicipalityChange() {
    filterForm.value.barangay = '';
    applyFilters();
}

function applyFilters() {
    router.get('/reports', filterForm.value, { preserveState: true });
}

function resetFilters() {
    filterForm.value = {
        project_id: '',
        province_prefix: '',
        district: '',
        municipality: '',
        barangay: '',
        status: '',
    };
    router.get('/reports');
}

const activeGeoScope = computed(() => {
    const parts = [];
    if (filterForm.value.province_prefix) {
        const p = props.provinces.find(prov => prov.prefix === filterForm.value.province_prefix);
        if (p && p.name !== 'All Region 2') parts.push(p.name);
    }
    if (filterForm.value.district) parts.push(filterForm.value.district);
    if (filterForm.value.municipality) parts.push(filterForm.value.municipality);
    if (filterForm.value.barangay) parts.push('Brgy. ' + filterForm.value.barangay);

    return parts.length > 0 ? parts.join(' • ') : 'All Region 02';
});

const excelExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (filterForm.value.project_id) params.append('project_id', filterForm.value.project_id);
    if (filterForm.value.province_prefix) params.append('province_prefix', filterForm.value.province_prefix);
    if (filterForm.value.district) params.append('district', filterForm.value.district);
    if (filterForm.value.municipality) params.append('municipality', filterForm.value.municipality);
    if (filterForm.value.barangay) params.append('barangay', filterForm.value.barangay);
    if (filterForm.value.status) params.append('status', filterForm.value.status);
    return '/reports/export/excel?' + params.toString();
});

const csvExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (filterForm.value.project_id) params.append('project_id', filterForm.value.project_id);
    if (filterForm.value.province_prefix) params.append('province_prefix', filterForm.value.province_prefix);
    if (filterForm.value.district) params.append('district', filterForm.value.district);
    if (filterForm.value.municipality) params.append('municipality', filterForm.value.municipality);
    if (filterForm.value.barangay) params.append('barangay', filterForm.value.barangay);
    if (filterForm.value.status) params.append('status', filterForm.value.status);
    return '/reports/export/csv?' + params.toString();
});

function printDossier() {
    window.print();
}

function formatStatusLabel(st) {
    if (!st) return '';
    return st.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Modal logic
const showModal = ref(false);
const recipientInput = ref('');
const subForm = useForm({
    title: '',
    frequency: 'weekly',
    format: 'xlsx',
    recipients: [],
    filter_config: {},
});

function openSubscriptionModal() {
    recipientInput.value = '';
    subForm.title = 'DICT Region 2 Free Wi-Fi ' + (filterForm.value.province_prefix ? 'Provincial' : 'Regional') + ' Accomplishment Report';
    subForm.frequency = 'weekly';
    subForm.format = 'xlsx';
    showModal.value = true;
}

function submitSubscription() {
    const emails = recipientInput.value.split(',').map(e => e.trim()).filter(Boolean);
    subForm.recipients = emails;
    subForm.filter_config = { ...filterForm.value };

    subForm.post('/reports/subscriptions', {
        onSuccess: () => {
            showModal.value = false;
        },
    });
}

function cancelSubscription(id) {
    if (confirm('Cancel this scheduled report subscription?')) {
        router.delete('/reports/subscriptions/' + id);
    }
}
</script>

<style scoped>
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .page-header, .card, .grid-4 {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
    .print-only-header {
        display: block !important;
    }
    .btn, .nav-section, .sidebar, .header-telemetry, .app-sidebar, .topbar {
        display: none !important;
    }
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}

.modal-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 520px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--c-border);
}
</style>
