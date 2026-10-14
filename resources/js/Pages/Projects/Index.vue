<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Region 2 Projects Portfolio</h1>
                <p class="page-description">
                    Multi-program portfolio directory tracking initiatives across Batanes, Cagayan, Isabela, Nueva Vizcaya, and Quirino.
                </p>
            </div>
            <div class="page-actions" v-if="canCreate">
                <Link href="/projects/create" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>New Project</span>
                </Link>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="metric-grid" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <span class="metric-label">Total Portfolio</span>
                <span class="metric-value">{{ metrics.total_projects }}</span>
                <span class="metric-sub">Initiatives Defined</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Active Deployments</span>
                <span class="metric-value" style="color: var(--scale-pass);">{{ metrics.active_projects }}</span>
                <span class="metric-sub">Currently in execution</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Allocated Investment</span>
                <span class="metric-value" style="color: var(--c-primary); font-size: 1.45rem;">
                    {{ formatCurrency(metrics.total_budget) }}
                </span>
                <span class="metric-sub">GAA & Partner Funding</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Archetypes Distribution</span>
                <div style="display: flex; gap: 0.35rem; margin-top: 0.4rem; flex-wrap: wrap;">
                    <span class="badge badge-kind-a" :title="'Kind A (Site Deployment): ' + metrics.kind_counts.site">
                        A: {{ metrics.kind_counts.site }}
                    </span>
                    <span class="badge badge-kind-b" :title="'Kind B (Institution Adoption): ' + metrics.kind_counts.institution">
                        B: {{ metrics.kind_counts.institution }}
                    </span>
                    <span class="badge badge-kind-c" :title="'Kind C (Activities & GAD): ' + metrics.kind_counts.activity">
                        C: {{ metrics.kind_counts.activity }}
                    </span>
                    <span class="badge badge-kind-d" :title="'Kind D (Milestones & Studies): ' + metrics.kind_counts.milestone">
                        D: {{ metrics.kind_counts.milestone }}
                    </span>
                </div>
                <span class="metric-sub" style="margin-top: 0.25rem;">4 Tracking Models</span>
            </div>
        </div>

        <!-- Filter and Search Bar -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; align-items: center; justify-content: space-between;">
                <!-- Kind Filter Pills -->
                <div class="tabs-nav" style="overflow-x: auto; max-width: 100%;">
                    <button class="tab-btn" :class="{ active: !currentKind }" @click="filterKind(null)">All Kinds</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'site' }" @click="filterKind('site')">Kind A (Sites)</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'institution' }" @click="filterKind('institution')">Kind B (Institutions)</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'activity' }" @click="filterKind('activity')">Kind C (Activities)</button>
                    <button class="tab-btn" :class="{ active: currentKind === 'milestone' }" @click="filterKind('milestone')">Kind D (Studies)</button>
                </div>

                <!-- Program, Lifecycle and Search Controls -->
                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <select v-model="selectedProgram" @change="applyFilters" class="form-control" style="width: auto; min-width: 180px; padding: 0.4rem 0.65rem; font-size: 0.82rem;">
                        <option value="">All Programs (29)</option>
                        <option v-for="prog in programs" :key="prog.id" :value="prog.id">
                            {{ prog.name }}
                        </option>
                    </select>

                    <select v-model="selectedLifecycle" @change="applyFilters" class="form-control" style="width: auto; min-width: 120px; padding: 0.4rem 0.65rem; font-size: 0.82rem;">
                        <option value="">All Lifecycles</option>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                        <option value="archived">Archived</option>
                    </select>

                    <input
                        type="text"
                        v-model="searchQuery"
                        @keyup.enter="applyFilters"
                        placeholder="Search code or title..."
                        class="form-control"
                        style="width: 200px; padding: 0.4rem 0.65rem; font-size: 0.82rem;"
                    />

                    <button class="btn btn-secondary btn-sm" @click="resetFilters" v-if="hasActiveFilters">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div v-if="projects.length > 0" class="portfolio-grid">
            <div v-for="proj in projects" :key="proj.id" class="project-card">
                <div>
                    <div class="project-card-header">
                        <span class="project-code-badge">{{ proj.code }}</span>
                        <div style="display: flex; gap: 0.35rem; align-items: center;">
                            <span :class="['badge', getKindBadgeClass(proj.project_type?.kind)]">
                                {{ getKindShortLabel(proj.project_type?.kind) }}
                            </span>
                            <span :class="['badge', getLifecycleBadgeClass(proj.lifecycle)]">
                                {{ proj.lifecycle }}
                            </span>
                        </div>
                    </div>

                    <h3 class="project-title">{{ proj.name }}</h3>

                    <div class="project-meta-row" style="margin-top: 0.65rem;">
                        <span v-if="proj.program" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 500;">
                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%;" :style="{ background: proj.program.color || 'var(--c-primary)' }"></span>
                            {{ proj.program.name }}
                        </span>
                    </div>

                    <div class="project-meta-row" style="margin-top: 0.45rem;">
                        <span v-if="proj.budget" class="project-budget-pill">
                            {{ formatCurrency(proj.budget) }}
                        </span>
                        <span v-if="proj.funding_source" style="font-size: 0.76rem; color: var(--c-ink-muted);">
                            • {{ proj.funding_source }}
                        </span>
                    </div>

                    <div v-if="proj.start_date || proj.end_date" class="project-meta-row" style="margin-top: 0.45rem; font-size: 0.76rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                            <line x1="16" x2="16" y1="2" y2="6"></line>
                            <line x1="8" x2="8" y1="2" y2="6"></line>
                            <line x1="3" x2="21" y1="10" y2="10"></line>
                        </svg>
                        <span>{{ proj.start_date || 'TBD' }} → {{ proj.end_date || 'Ongoing' }}</span>
                    </div>

                    <!-- Geographic Scope Chips -->
                    <div style="margin-top: 0.85rem;">
                        <div style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; color: var(--c-ink-subtle); margin-bottom: 0.25rem;">
                            Geographic Coverage ({{ proj.scopes?.length || 0 }} Areas)
                        </div>
                        <div class="project-scopes-box">
                            <span v-for="scope in (proj.scopes || []).slice(0, 4)" :key="scope.id" class="scope-chip">
                                {{ scope.area?.name || scope.psgc_code }}
                            </span>
                            <span v-if="(proj.scopes?.length || 0) > 4" class="scope-chip highlight">
                                +{{ proj.scopes.length - 4 }} more
                            </span>
                            <span v-if="!proj.scopes || proj.scopes.length === 0" class="scope-chip">
                                Region 2 Wide
                            </span>
                        </div>
                    </div>

                    <!-- Indicator Targets Preview -->
                    <div v-if="proj.indicator_targets && proj.indicator_targets.length > 0" style="margin-top: 0.85rem; padding: 0.65rem 0.85rem; background: var(--c-surface-subtle); border-radius: var(--radius-sm); border: 1px solid var(--c-border-subtle);">
                        <div style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; color: var(--c-ink-subtle); margin-bottom: 0.35rem;">
                            Performance Targets
                        </div>
                        <div v-for="tgt in proj.indicator_targets.slice(0, 2)" :key="tgt.id" style="display: flex; justify-content: space-between; font-size: 0.76rem; font-family: var(--font-mono); margin-bottom: 0.2rem;">
                            <span style="color: var(--c-ink-muted);">{{ tgt.indicator_key }} ({{ tgt.period }})</span>
                            <span style="font-weight: 700; color: var(--c-ink);">{{ Number(tgt.target_value).toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer" style="padding: 0.75rem 0; margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; background: transparent; border-top: 1px solid var(--c-border-subtle);">
                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                        <span style="width: 8px; height: 8px; border-radius: 50%;" :style="{ background: proj.project_type?.color_code || 'var(--c-primary)' }"></span>
                        <span style="font-size: 0.75rem; color: var(--c-ink-muted);">{{ proj.project_type?.name }}</span>
                    </div>
                    <Link :href="'/map?project_id=' + proj.id" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.3rem 0.65rem;">
                        <span>View Map</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card" style="padding: 3rem 1.5rem; text-align: center;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-subtle)" stroke-width="1.5" style="margin: 0 auto 1rem auto; display: block;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--c-ink); margin-bottom: 0.35rem;">No Projects Found</h3>
            <p style="font-size: 0.88rem; color: var(--c-ink-muted); max-width: 420px; margin: 0 auto 1.5rem auto;">
                No project matches your active search and filter parameters. Try clearing the filters or create a new project.
            </p>
            <button class="btn btn-secondary btn-sm" @click="resetFilters">
                Reset All Filters
            </button>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
    programs: {
        type: Array,
        default: () => [],
    },
    projectTypes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

const searchQuery = ref(props.filters.search || '');
const currentKind = ref(props.filters.kind || null);
const selectedProgram = ref(props.filters.program_id || '');
const selectedLifecycle = ref(props.filters.lifecycle || '');

const hasActiveFilters = computed(() => {
    return !!(searchQuery.value || currentKind.value || selectedProgram.value || selectedLifecycle.value);
});

function applyFilters() {
    router.get('/projects', {
        search: searchQuery.value || undefined,
        kind: currentKind.value || undefined,
        program_id: selectedProgram.value || undefined,
        lifecycle: selectedLifecycle.value || undefined,
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
    selectedProgram.value = '';
    selectedLifecycle.value = '';
    applyFilters();
}

function formatCurrency(amount) {
    if (!amount) return '₱0.00';
    const num = Number(amount);
    if (num >= 1000000) {
        return '₱' + (num / 1000000).toFixed(2) + 'M';
    }
    return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getKindBadgeClass(kind) {
    switch (kind) {
        case 'site': return 'badge-kind-a';
        case 'institution': return 'badge-kind-b';
        case 'activity': return 'badge-kind-c';
        case 'milestone': return 'badge-kind-d';
        default: return 'badge-secondary';
    }
}

function getKindShortLabel(kind) {
    switch (kind) {
        case 'site': return 'Kind A';
        case 'institution': return 'Kind B';
        case 'activity': return 'Kind C';
        case 'milestone': return 'Kind D';
        default: return 'Project';
    }
}

function getLifecycleBadgeClass(lifecycle) {
    switch (lifecycle) {
        case 'active': return 'badge-pass';
        case 'draft': return 'badge-warning';
        case 'closed': return 'badge-secondary';
        case 'archived': return 'badge-neutral';
        default: return 'badge-secondary';
    }
}
</script>
