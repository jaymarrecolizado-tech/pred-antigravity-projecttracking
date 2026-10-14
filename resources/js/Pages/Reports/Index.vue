<template>
    <AppShell>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-group">
                <h1>Accomplishment Reporting & Executive Dispatch</h1>
                <div class="page-subtitle">
                    Official regional progress matrices, OpenSpout Excel streaming, and automated email subscriptions
                </div>
            </div>
            <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                <button type="button" @click="openSubscriptionModal" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>Schedule Subscription</span>
                </button>
                <button type="button" @click="printDossier" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>Print Executive Dossier</span>
                </button>
                <a :href="excelExportUrl" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" x2="12" y1="15" y2="3"></line>
                    </svg>
                    <span>Export Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- Printable Executive Header (Visible only on Print) -->
        <div class="print-only-header" style="display: none;">
            <div style="text-align: center; margin-bottom: 1.5rem; border-bottom: 2px solid #000; padding-bottom: 1rem;">
                <h2 style="font-size: 1.2rem; margin: 0; text-transform: uppercase;">Republic of the Philippines</h2>
                <h1 style="font-size: 1.4rem; margin: 0.25rem 0; font-weight: 800;">DEPARTMENT OF INFORMATION AND COMMUNICATIONS TECHNOLOGY</h1>
                <div style="font-size: 0.95rem; font-weight: 600;">Regional Office 02 — Cagayan Valley</div>
                <div style="font-size: 0.85rem; margin-top: 0.35rem; color: #555;">REGIONAL INFRASTRUCTURE & PROGRAM ROLLOUT ACCOMPLISHMENT DOSSIER</div>
            </div>
        </div>

        <!-- Filter Controls Card -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <div style="font-weight: 700; font-size: 0.9rem; color: var(--c-ink); margin-bottom: 0.75rem;">
                Query & Accomplishment Filter Matrix
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
                <div>
                    <label class="form-label" style="font-size: 0.75rem;">Initiative / Project</label>
                    <select v-model="filterForm.project_id" @change="applyFilters" class="form-input">
                        <option value="">All Region 2 Projects</option>
                        <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                            {{ proj.code }} - {{ proj.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.75rem;">Province Geographic Scope</label>
                    <select v-model="filterForm.province_prefix" @change="applyFilters" class="form-input">
                        <option v-for="prov in provinces" :key="prov.prefix" :value="prov.prefix">
                            {{ prov.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.75rem;">Deployment Status</label>
                    <select v-model="filterForm.status" @change="applyFilters" class="form-input">
                        <option value="">All Statuses</option>
                        <option v-for="st in statuses" :key="st" :value="st">
                            {{ formatStatusLabel(st) }}
                        </option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" @click="resetFilters" class="btn btn-ghost" style="flex: 1;">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- 4-Stat Filtered KPI Ribbon -->
        <div class="grid-4" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Sites In Filter</span>
                    <span class="badge badge-neutral">Filtered Scope</span>
                </div>
                <div class="metric-value">{{ matrix.total_sites }}</div>
                <div class="metric-subtext">Active monitoring targets</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Completed Sites</span>
                    <span class="badge badge-success">Milestones Met</span>
                </div>
                <div class="metric-value">{{ matrix.completed_sites }}</div>
                <div class="metric-subtext">{{ matrix.operational_sites }} live operational</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Accomplishment Rate</span>
                    <span style="color: var(--scale-success); font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem;">
                        {{ matrix.accomplishment_rate }}%
                    </span>
                </div>
                <div class="metric-value">{{ matrix.accomplishment_rate }}%</div>
                <div class="metric-subtext">Percentage of target achieved</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Scheduled Subscriptions</span>
                    <span class="badge badge-primary">{{ subscriptions.length }} Active</span>
                </div>
                <div class="metric-value">{{ subscriptions.length }}</div>
                <div class="metric-subtext">Automated dispatches</div>
            </div>
        </div>

        <!-- Preview Matrix Table -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <div>
                    <div class="card-title">Accomplishment Preview Matrix</div>
                    <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                        Displaying top {{ matrix.preview_rows.length }} of {{ matrix.total_sites }} matching records (full dataset included in Excel export)
                    </div>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Site Code</th>
                            <th>Initiative</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Target Date</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="matrix.preview_rows.length === 0">
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--c-ink-muted);">
                                No site records match the specified query filters.
                            </td>
                        </tr>
                        <tr v-for="row in matrix.preview_rows" :key="row.id">
                            <td style="font-family: var(--font-mono); font-weight: 700; color: var(--c-ink);">
                                {{ row.site_code }}
                            </td>
                            <td>{{ row.project_name }}</td>
                            <td>{{ row.province }}</td>
                            <td>{{ row.municipality }}</td>
                            <td style="font-family: var(--font-mono); font-size: 0.78rem;">{{ row.target_date }}</td>
                            <td>
                                <span class="chip-status" :class="row.status === 'operational' ? 'status-online' : 'status-ongoing'">
                                    <span class="status-dot"></span>
                                    <span>{{ formatStatusLabel(row.status) }}</span>
                                </span>
                            </td>
                            <td>
                                <Link :href="'/sites/' + row.id" class="btn btn-ghost btn-sm">Dossier</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Report Subscriptions Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Automated Scheduled Report Subscriptions</div>
                    <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                        Recurring executive digests automatically compiled and dispatched via console cron
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div v-if="subscriptions.length === 0" style="padding: 1.5rem; text-align: center; color: var(--c-ink-muted);">
                    You have no active automated report subscriptions. Click "Schedule Subscription" above to create one.
                </div>
                <div v-else style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div v-for="sub in subscriptions" :key="sub.id"
                         style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--c-border); border-radius: var(--radius-md); flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <strong style="color: var(--c-ink);">{{ sub.title }}</strong>
                                <span class="badge badge-primary">{{ sub.frequency.toUpperCase() }}</span>
                                <span class="badge badge-neutral">{{ sub.format.toUpperCase() }}</span>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem; font-family: var(--font-mono);">
                                Recipients: {{ sub.recipients.join(', ') }} • Last Dispatched: {{ sub.last_dispatched_at || 'Pending initial cycle' }}
                            </div>
                        </div>

                        <button type="button" @click="cancelSubscription(sub.id)" class="btn btn-danger btn-sm">
                            Cancel Subscription
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Subscription Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
            <div class="modal-card">
                <div class="modal-header">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--c-ink); margin: 0;">Schedule Automated Report Subscription</h3>
                    <button type="button" @click="showModal = false" class="btn btn-ghost btn-sm">✕</button>
                </div>
                <form @submit.prevent="submitSubscription" style="padding: 1.25rem;">
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Subscription Title</label>
                        <input type="text" v-model="subForm.title" class="form-input" placeholder="e.g. Region 2 Weekly Executive Accomplishment" required />
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label">Frequency</label>
                            <select v-model="subForm.frequency" class="form-input" required>
                                <option value="daily">Daily Digest</option>
                                <option value="weekly">Weekly (Every Monday)</option>
                                <option value="monthly">Monthly (1st of Month)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Format</label>
                            <select v-model="subForm.format" class="form-input" required>
                                <option value="xlsx">Excel Workbook (.xlsx)</option>
                                <option value="pdf">Executive PDF Dossier (.pdf)</option>
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
    status: props.filters.status || '',
});

function applyFilters() {
    router.get('/reports', filterForm.value, { preserveState: true });
}

function resetFilters() {
    filterForm.value = { project_id: '', province_prefix: '', status: '' };
    router.get('/reports');
}

const excelExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (filterForm.value.project_id) params.append('project_id', filterForm.value.project_id);
    if (filterForm.value.province_prefix) params.append('province_prefix', filterForm.value.province_prefix);
    if (filterForm.value.status) params.append('status', filterForm.value.status);
    return '/reports/export/excel?' + params.toString();
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
    subForm.title = 'Region 2 ' + (filterForm.value.province_prefix ? 'Provincial' : 'Regional') + ' Accomplishment Report';
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
    .btn, .nav-section, .sidebar, .header-telemetry {
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
