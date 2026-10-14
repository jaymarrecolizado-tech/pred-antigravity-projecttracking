<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Institutions Registry</h1>
                <p class="page-description">
                    Centralized directory of Local Government Units (LGUs), DepEd Schools, State Universities (SUCs), and Health Centers adopting DICT digital systems.
                </p>
            </div>
            <div class="page-actions" v-if="canManage">
                <button class="btn btn-primary" @click="showModal = true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Register Institution</span>
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="metric-grid" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <span class="metric-label">Total Institutions</span>
                <span class="metric-value">{{ stats.total_institutions }}</span>
                <span class="metric-sub">Registered entities</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Local Government Units</span>
                <span class="metric-value" style="color: var(--scale-pass);">{{ stats.lgu_count }}</span>
                <span class="metric-sub">Provincial & municipal LGUs</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">DepEd Public Schools</span>
                <span class="metric-value" style="color: var(--c-primary);">{{ stats.school_count }}</span>
                <span class="metric-sub">Tagged with School IDs</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">State Universities (SUCs)</span>
                <span class="metric-value" style="color: var(--scale-warning);">{{ stats.suc_count }}</span>
                <span class="metric-sub">CSU, ISU, NVSU, QSU campuses</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; align-items: center; justify-content: space-between;">
                <div class="tabs-nav" style="overflow-x: auto;">
                    <button class="tab-btn" :class="{ active: !currentKind }" @click="filterKind(null)">All Entities</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'lgu' }" @click="filterKind('lgu')">LGUs</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'deped_school' }" @click="filterKind('deped_school')">DepEd Schools</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'suc' }" @click="filterKind('suc')">SUC Campuses</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'hospital_clinic' }" @click="filterKind('hospital_clinic')">Hospitals & RHUs</button>
                </div>

                <div style="display: flex; gap: 0.65rem; align-items: center;">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @keyup.enter="applyFilters"
                        placeholder="Search name or ID..."
                        class="form-control"
                        style="width: 220px; font-size: 0.82rem;"
                    />
                    <button class="btn btn-secondary btn-sm" @click="resetFilters" v-if="searchQuery || currentKind">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Institutions Table -->
        <div class="card" style="overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Institution Name</th>
                            <th>Entity Classification</th>
                            <th>External Registry ID</th>
                            <th>Primary Physical Location</th>
                            <th>Focal Contact Person</th>
                            <th>Active Project Links</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="inst in institutions.data" :key="inst.id">
                            <td>
                                <div style="font-weight: 600; color: var(--c-ink); font-size: 0.9rem;">
                                    {{ inst.name }}
                                </div>
                                <div v-if="inst.area" style="font-size: 0.74rem; color: var(--c-ink-muted);">
                                    PSGC: {{ inst.psgc_code }} • {{ inst.area.name }}
                                </div>
                            </td>
                            <td>
                                <span :class="['badge', getKindBadge(inst.kind)]">
                                    {{ formatKindLabel(inst.kind) }}
                                </span>
                            </td>
                            <td class="font-mono" style="font-size: 0.78rem;">
                                {{ inst.external_id || '—' }}
                            </td>
                            <td>
                                <div v-if="inst.primary_location">
                                    <div style="font-size: 0.82rem; font-weight: 500; color: var(--c-ink);">
                                        {{ inst.primary_location.name }}
                                    </div>
                                    <div class="font-mono" style="font-size: 0.72rem; color: var(--c-ink-subtle);">
                                        [{{ Number(inst.primary_location.lat).toFixed(4) }}, {{ Number(inst.primary_location.lng).toFixed(4) }}]
                                    </div>
                                </div>
                                <span v-else style="color: var(--c-ink-subtle); font-size: 0.78rem;">No Anchor</span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--c-ink-muted);">
                                <div style="font-weight: 500; color: var(--c-ink);">{{ inst.contact_person || '—' }}</div>
                                <div style="font-size: 0.74rem;">{{ inst.contact_email || inst.contact_phone || '' }}</div>
                            </td>
                            <td>
                                <div v-if="inst.project_sites && inst.project_sites.length > 0" style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    <span v-for="ps in inst.project_sites" :key="ps.id" class="scope-chip" style="font-size: 0.7rem;">
                                        {{ ps.project?.code || 'Site' }}
                                    </span>
                                </div>
                                <span v-else style="color: var(--c-ink-subtle); font-size: 0.75rem;">None</span>
                            </td>
                        </tr>
                        <tr v-if="institutions.data.length === 0">
                            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--c-ink-muted);">
                                No institutions found matching criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="card-footer" v-if="institutions.links && institutions.links.length > 3" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.8rem; color: var(--c-ink-muted);">
                    Showing {{ institutions.from }} to {{ institutions.to }} of {{ institutions.total }} institutions
                </span>
                <div style="display: flex; gap: 0.25rem;">
                    <Link
                        v-for="(link, idx) in institutions.links"
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

        <!-- Registration Modal -->
        <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
            <div class="modal-card" style="max-width: 580px;">
                <div class="modal-header">
                    <h3 class="modal-title">Register New Institution</h3>
                    <button class="modal-close" @click="showModal = false">✕</button>
                </div>
                <form @submit.prevent="submitInstitution">
                    <div class="modal-body">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label required">Entity Classification</label>
                            <select v-model="instForm.kind" class="form-control" required>
                                <option value="lgu">Local Government Unit (LGU)</option>
                                <option value="deped_school">DepEd Public School</option>
                                <option value="suc">State University / College (SUC)</option>
                                <option value="hospital_clinic">Hospital / Rural Health Unit (RHU)</option>
                                <option value="nga_regional_office">National Gov Agency (NGA) Regional Office</option>
                                <option value="other">Other Public Institution</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label required">Institution Name</label>
                            <input
                                type="text"
                                v-model="instForm.name"
                                placeholder="e.g. City Government of Cauayan"
                                class="form-control"
                                required
                            />
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label class="form-label">External Registry ID</label>
                                <input
                                    type="text"
                                    v-model="instForm.external_id"
                                    placeholder="e.g. DEPED-102941"
                                    class="form-control font-mono"
                                />
                            </div>

                            <div class="form-group">
                                <label class="form-label required">PSGC Code (10-char)</label>
                                <input
                                    type="text"
                                    v-model="instForm.psgc_code"
                                    placeholder="e.g. 023108000"
                                    class="form-control font-mono"
                                    required
                                />
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Primary Physical Location Anchor</label>
                            <select v-model="instForm.primary_location_id" class="form-control">
                                <option :value="null">None (Standalone Institution)</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }}
                                </option>
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Focal Officer Name</label>
                                <input type="text" v-model="instForm.contact_person" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Focal Email</label>
                                <input type="email" v-model="instForm.contact_email" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="instForm.processing">
                            Save Institution
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    institutions: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    provinces: {
        type: Array,
        default: () => [],
    },
    locations: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const searchQuery = ref(props.filters.search || '');
const currentKind = ref(props.filters.kind || null);
const showModal = ref(false);

const instForm = useForm({
    kind: 'lgu',
    name: '',
    external_id: '',
    psgc_code: '021528000',
    primary_location_id: null,
    contact_person: '',
    contact_email: '',
    contact_phone: '',
});

function applyFilters() {
    router.get('/institutions', {
        search: searchQuery.value || undefined,
        kind: currentKind.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function filterKind(kind) {
    currentKind.value = kind;
    applyFilters();
}

function resetFilters() {
    searchQuery.value = '';
    currentKind.value = null;
    applyFilters();
}

function formatKindLabel(kind) {
    switch (kind) {
        case 'lgu': return 'Local Gov Unit';
        case 'deped_school': return 'DepEd School';
        case 'suc': return 'State University';
        case 'hospital_clinic': return 'Hospital / RHU';
        case 'nga_regional_office': return 'Regional Agency';
        default: return 'Institution';
    }
}

function getKindBadge(kind) {
    switch (kind) {
        case 'lgu': return 'badge-kind-b';
        case 'deped_school': return 'badge-kind-a';
        case 'suc': return 'badge-kind-d';
        case 'hospital_clinic': return 'badge-pass';
        default: return 'badge-secondary';
    }
}

function submitInstitution() {
    instForm.post('/institutions', {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            instForm.reset();
        },
    });
}
</script>
