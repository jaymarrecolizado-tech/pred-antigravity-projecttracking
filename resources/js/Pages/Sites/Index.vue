<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Project Sites Registry</h1>
                <p class="page-description">
                    Centralized registry of physical deployment sites, connectivity nodes, and institutional installations across Region 2.
                </p>
            </div>
            <div class="page-actions" v-if="canEnroll">
                <button class="btn btn-secondary" @click="showImportModal = true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Bulk Import</span>
                </button>
                <Link href="/sites/create" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Enroll Site</span>
                </Link>
            </div>
        </div>

        <!-- Telemetry Cards -->
        <div class="metric-grid" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <span class="metric-label">Enrolled Deployments</span>
                <span class="metric-value">{{ stats.total_sites }}</span>
                <span class="metric-sub">Active project instances</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Operational / Live</span>
                <span class="metric-value" style="color: var(--scale-pass);">{{ stats.operational_count }}</span>
                <span class="metric-sub">Passing telemetry benchmarks</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">In-Progress Pipeline</span>
                <span class="metric-value" style="color: var(--scale-warning);">{{ stats.in_progress_count }}</span>
                <span class="metric-sub">Survey, mounting, or QA</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Shared Physical Points</span>
                <span class="metric-value" style="color: var(--c-primary);">{{ stats.locations_count }}</span>
                <span class="metric-sub">Deduplicated locations</span>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between;">
                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap; flex: 1;">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @keyup.enter="applyFilters"
                        placeholder="Search site code, location or institution..."
                        class="form-control"
                        style="width: 260px; font-size: 0.82rem;"
                    />

                    <select v-model="selectedProject" @change="applyFilters" class="form-control" style="width: auto; min-width: 180px; font-size: 0.82rem;">
                        <option value="">All Projects</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">
                            {{ p.code }} • {{ p.name }}
                        </option>
                    </select>

                    <select v-model="selectedProvince" @change="applyFilters" class="form-control" style="width: auto; min-width: 140px; font-size: 0.82rem;">
                        <option value="">All Provinces</option>
                        <option v-for="prov in provinces" :key="prov.code" :value="prov.code">
                            {{ prov.name }}
                        </option>
                    </select>

                    <select v-model="selectedStatus" @change="applyFilters" class="form-control" style="width: auto; min-width: 130px; font-size: 0.82rem;">
                        <option value="">All Statuses</option>
                        <option value="operational">Operational</option>
                        <option value="installed">Installed</option>
                        <option value="survey_conducted">Survey Conducted</option>
                        <option value="planned">Planned</option>
                        <option value="live_production">Live in Production</option>
                        <option value="staff_trained">Staff Trained</option>
                        <option value="mou_signed">MOU Signed</option>
                    </select>

                    <button class="btn btn-secondary btn-sm" @click="resetFilters" v-if="hasActiveFilters">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Sites Table Card -->
        <div class="card" style="overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Site Identifier</th>
                            <th>Target Initiative</th>
                            <th>Physical Location & Category</th>
                            <th>Administrative Jurisdiction</th>
                            <th>Coordinates</th>
                            <th>Status Lifecycle</th>
                            <th>Target Date</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="site in sites.data" :key="site.id">
                            <td>
                                <span class="project-code-badge">{{ site.site_code || ('SITE-' + site.id) }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--c-ink); font-size: 0.86rem;">
                                    {{ site.project?.name }}
                                </div>
                                <div v-if="site.project?.program" style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.74rem; color: var(--c-ink-muted);">
                                    <span style="width: 6px; height: 6px; border-radius: 50%;" :style="{ background: site.project.program.color || 'var(--c-primary)' }"></span>
                                    <span>{{ site.project.program.name }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--c-ink);">
                                    {{ site.location?.name || site.institution?.name || 'Unassigned Location' }}
                                </div>
                                <div v-if="site.location?.category" style="margin-top: 0.2rem;">
                                    <span class="badge" style="background: var(--c-surface-subtle); border-color: var(--c-border); font-size: 0.68rem;">
                                        {{ site.location.category.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </div>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--c-ink-muted);">
                                <div v-if="site.location?.barangay">
                                    <div style="font-weight: 500; color: var(--c-ink);">{{ site.location.barangay.name }}</div>
                                    <div style="font-size: 0.74rem;">
                                        {{ site.location.barangay.parent?.name }}, {{ site.location.barangay.parent?.parent?.name }}
                                    </div>
                                </div>
                                <div v-else style="color: var(--c-ink-subtle);">Regional Office</div>
                            </td>
                            <td class="font-mono" style="font-size: 0.78rem;">
                                <div v-if="site.location?.lat">
                                    {{ Number(site.location.lat).toFixed(4) }}, {{ Number(site.location.lng).toFixed(4) }}
                                    <span v-if="site.location.elevation_m" style="color: var(--c-ink-subtle); display: block; font-size: 0.7rem;">
                                        {{ site.location.elevation_m }}m elev
                                    </span>
                                </div>
                                <span v-else style="color: var(--c-ink-subtle);">None</span>
                            </td>
                            <td>
                                <span :class="['badge', getStatusBadgeClass(site.current_status)]">
                                    {{ site.current_status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--c-ink-muted);">
                                {{ site.target_completion_date || '—' }}
                            </td>
                            <td style="text-align: right;">
                                <Link
                                    v-if="site.location?.lat"
                                    :href="'/map?lat=' + site.location.lat + '&lng=' + site.location.lng + '&zoom=14'"
                                    class="btn btn-ghost btn-sm"
                                    title="View on Interactive Map"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon>
                                        <line x1="9" x2="9" y1="3" y2="18"></line>
                                        <line x1="15" x2="15" y1="6" y2="21"></line>
                                    </svg>
                                    <span>Map</span>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="sites.data.length === 0">
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: var(--c-ink-muted);">
                                No project sites enrolled matching current filter criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="card-footer" v-if="sites.links && sites.links.length > 3" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.8rem; color: var(--c-ink-muted);">
                    Showing {{ sites.from }} to {{ sites.to }} of {{ sites.total }} sites
                </span>
                <div style="display: flex; gap: 0.25rem;">
                    <Link
                        v-for="(link, idx) in sites.links"
                        :key="idx"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="btn btn-sm"
                        :class="{ 'btn-primary': link.active, 'btn-secondary': !link.active, 'disabled': !link.url }"
                        style="padding: 0.3rem 0.6rem; font-size: 0.78rem;"
                    />
                </div>
            </div>
        </div>

        <!-- Bulk Import Modal -->
        <div v-if="showImportModal" class="modal-backdrop" @click.self="showImportModal = false">
            <div class="modal-card" style="max-width: 620px;">
                <div class="modal-header">
                    <h3 class="modal-title">Bulk Site Enrollment</h3>
                    <button class="modal-close" @click="showImportModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <p style="font-size: 0.84rem; color: var(--c-ink-muted); margin-bottom: 1rem;">
                        Paste formatted JSON rows or template list to batch enroll sites. Automatic duplicate GPS proximity checking (&lt;50m) and PSGC validation will be applied.
                    </p>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label required">Target Project</label>
                        <select v-model="importProjectId" class="form-control" required>
                            <option value="">Select Project</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">
                                {{ p.code }} • {{ p.name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Sites Payload (JSON Array)</label>
                        <textarea
                            v-model="importPayloadString"
                            rows="8"
                            class="form-control font-mono"
                            style="font-size: 0.78rem;"
                            placeholder='[
  { "name": "San Mariano Municipal Plaza", "psgc_barangay": "023126001", "lat": 16.9850, "lng": 122.0120, "category": "plaza" }
]'
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="showImportModal = false">Cancel</button>
                    <button class="btn btn-primary" @click="executeImport" :disabled="isImporting">
                        {{ isImporting ? 'Enrolling Sites...' : 'Commit Batch Enrollment' }}
                    </button>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    sites: {
        type: Object,
        required: true,
    },
    projects: {
        type: Array,
        default: () => [],
    },
    provinces: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canEnroll: {
        type: Boolean,
        default: false,
    },
});

const searchQuery = ref(props.filters.search || '');
const selectedProject = ref(props.filters.project_id || '');
const selectedProvince = ref(props.filters.province_code || '');
const selectedStatus = ref(props.filters.status || '');

const hasActiveFilters = computed(() => {
    return !!(searchQuery.value || selectedProject.value || selectedProvince.value || selectedStatus.value);
});

function applyFilters() {
    router.get('/sites', {
        search: searchQuery.value || undefined,
        project_id: selectedProject.value || undefined,
        province_code: selectedProvince.value || undefined,
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    searchQuery.value = '';
    selectedProject.value = '';
    selectedProvince.value = '';
    selectedStatus.value = '';
    applyFilters();
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'operational':
        case 'live_production':
        case 'fully_adopted':
        case 'graduated_certified':
            return 'badge-pass';
        case 'installed':
        case 'staff_trained':
        case 'mou_signed':
        case 'survey_conducted':
            return 'badge-warning';
        case 'decommissioned':
        case 'cancelled':
        case 'dropped':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

// Bulk Import Logic
const showImportModal = ref(false);
const importProjectId = ref('');
const importPayloadString = ref('');
const isImporting = ref(false);

function executeImport() {
    if (!importProjectId.value) {
        alert('Please choose a target project.');
        return;
    }

    let rows;
    try {
        rows = JSON.parse(importPayloadString.value);
        if (!Array.isArray(rows) || rows.length === 0) {
            throw new Error('Must be a non-empty array of objects.');
        }
    } catch (e) {
        alert('Invalid JSON array payload: ' + e.message);
        return;
    }

    isImporting.value = true;
    router.post('/sites/import', {
        project_id: importProjectId.value,
        rows: rows,
    }, {
        onSuccess: () => {
            showImportModal.value = false;
            importPayloadString.value = '';
            isImporting.value = false;
        },
        onError: () => {
            isImporting.value = false;
        },
    });
}
</script>
