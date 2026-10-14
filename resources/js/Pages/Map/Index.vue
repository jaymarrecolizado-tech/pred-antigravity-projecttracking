<template>
    <AppShell>
        <div class="map-page-wrapper">
            <!-- Floating Top Control Ribbon (NMS Reference) -->
            <div class="map-floating-ribbon">
                <div class="ribbon-metric-pill">
                    <span class="ribbon-label">POINTS</span>
                    <span class="ribbon-val">{{ activePointsCount }} / {{ summary.total_sites }}</span>
                </div>

                <div class="ribbon-divider"></div>

                <!-- Metric Selector -->
                <div class="ribbon-control-group">
                    <label class="ribbon-label">METRIC</label>
                    <select v-model="selectedMetric" class="ribbon-select">
                        <option value="completion">Completion Rate %</option>
                        <option value="operational">Operational / Online Rate %</option>
                    </select>
                </div>

                <div class="ribbon-divider"></div>

                <!-- Basemap Selector -->
                <div class="ribbon-control-group">
                    <label class="ribbon-label">BASEMAP</label>
                    <select v-model="selectedBasemap" class="ribbon-select" title="Switch map basemap style">
                        <option value="canvas">Clean Canvas (No Watermark)</option>
                        <option value="osm">OpenStreetMap</option>
                        <option value="satellite">Satellite Imagery</option>
                    </select>
                </div>

                <div class="ribbon-divider"></div>

                <!-- Merge <---> Detail Granularity Slider (NMS Reference) -->
                <div class="ribbon-control-group slider-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span class="ribbon-label">GRANULARITY</span>
                        <span class="ribbon-sublabel">Merge ←→ Detail</span>
                    </div>
                    <input
                        type="range"
                        min="25"
                        max="95"
                        step="5"
                        v-model.number="granularitySlider"
                        class="ribbon-slider"
                        title="Adjust cluster merge distance"
                    />
                </div>

                <div class="ribbon-divider"></div>

                <!-- Cluster Mode Toggle -->
                <div class="tabs-nav ribbon-tabs">
                    <button
                        type="button"
                        class="tab-btn"
                        :class="{ active: clusterMode === 'distance' }"
                        @click="clusterMode = 'distance'"
                    >
                        Distance Supercluster
                    </button>
                    <button
                        type="button"
                        class="tab-btn"
                        :class="{ active: clusterMode === 'admin' }"
                        @click="clusterMode = 'admin'"
                    >
                        Admin Rollup
                    </button>
                </div>

                <div class="ribbon-divider"></div>

                <!-- Filter Drawer Toggle Button -->
                <button
                    class="btn btn-secondary btn-sm ribbon-filter-btn"
                    :class="{ 'btn-primary': selectedProjectIds.length > 0 }"
                    @click="showFilterDrawer = !showFilterDrawer"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span>Portfolio Filter ({{ selectedProjectIds.length }}/{{ projects.length }})</span>
                </button>
            </div>

            <!-- Leaflet Map Canvas -->
            <MapView
                :features="initialGeojson.features"
                :metric="selectedMetric"
                :granularity="granularitySlider"
                :clusterMode="clusterMode"
                :basemap="selectedBasemap"
                :provinces="provinces"
                :selectedProjectIds="selectedProjectIds"
                :focusCoords="initialFocus"
                @select-cluster="onClusterSelected"
                @select-site="onSiteSelected"
            />

            <!-- Slide-Over Filter Drawer (Right Side) -->
            <div v-if="showFilterDrawer" class="map-drawer filter-drawer">
                <div class="drawer-header">
                    <div>
                        <h3 class="drawer-title">Portfolio Filter Tray</h3>
                        <p class="drawer-subtitle">Filter map points across initiatives and tracking archetypes.</p>
                    </div>
                    <button class="modal-close" @click="showFilterDrawer = false">✕</button>
                </div>

                <div class="drawer-body">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <button type="button" class="btn btn-ghost btn-sm" @click="selectAllProjects">
                            Select All
                        </button>
                        <button type="button" class="btn btn-ghost btn-sm" @click="clearProjectFilters">
                            Clear Filters
                        </button>
                    </div>

                    <!-- Filter by Archetype -->
                    <div v-for="kind in ['site', 'institution', 'activity', 'milestone']" :key="kind" style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span :class="['badge', getKindBadge(kind)]">
                                {{ getKindHeading(kind) }}
                            </span>
                            <span style="font-size: 0.72rem; color: var(--c-ink-muted);">
                                {{ getProjectsForKind(kind).length }} initiatives
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                            <label
                                v-for="p in getProjectsForKind(kind)"
                                :key="p.id"
                                class="project-filter-item"
                                :class="{ active: selectedProjectIds.includes(p.id) }"
                            >
                                <input
                                    type="checkbox"
                                    :value="p.id"
                                    v-model="selectedProjectIds"
                                />
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%;" :style="{ background: p.color || p.project_type?.color_code || 'var(--c-primary)' }"></span>
                                    <span class="project-filter-name">{{ p.name }}</span>
                                </div>
                                <span class="font-mono" style="font-size: 0.68rem; color: var(--c-ink-subtle);">{{ p.code }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flyout Dossier Drawer (When clicking a cluster or single site) -->
            <div v-if="activeSelection" class="map-drawer dossier-drawer">
                <div class="drawer-header">
                    <div>
                        <span v-if="activeSelection.isCluster" class="badge badge-pass" style="margin-bottom: 0.25rem;">
                            Cluster • {{ activeSelection.count }} Sites
                        </span>
                        <span v-else class="project-code-badge" style="margin-bottom: 0.25rem;">
                            {{ activeSelection.site.site_code }}
                        </span>
                        <h3 class="drawer-title">
                            {{ activeSelection.isCluster ? (activeSelection.name || 'Regional Cluster') : activeSelection.site.name }}
                        </h3>
                    </div>
                    <button class="modal-close" @click="activeSelection = null">✕</button>
                </div>

                <div class="drawer-body">
                    <!-- Cluster View -->
                    <div v-if="activeSelection.isCluster">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem; background: var(--c-surface-subtle); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--c-border);">
                            <div>
                                <span style="font-size: 0.72rem; color: var(--c-ink-muted); text-transform: uppercase;">Total Count</span>
                                <div style="font-size: 1.2rem; font-weight: 800; color: var(--c-ink);">{{ activeSelection.count }}</div>
                            </div>
                            <div>
                                <span style="font-size: 0.72rem; color: var(--c-ink-muted); text-transform: uppercase;">Accomplishment</span>
                                <div style="font-size: 1.2rem; font-weight: 800; color: var(--scale-pass);">
                                    {{ Math.round(activeSelection.percentage) }}%
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--c-ink-subtle); margin-bottom: 0.5rem;">
                            Sites in this cluster
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 480px; overflow-y: auto;">
                            <div
                                v-for="s in activeSelection.sites"
                                :key="s.id"
                                style="border: 1px solid var(--c-border); border-radius: var(--radius-md); padding: 0.75rem; background: var(--c-surface);"
                            >
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                    <span class="project-code-badge">{{ s.site_code }}</span>
                                    <span :class="['badge', s.is_operational ? 'badge-pass' : 'badge-warning']" style="font-size: 0.68rem;">
                                        {{ s.current_status }}
                                    </span>
                                </div>
                                <div style="font-weight: 600; font-size: 0.88rem; color: var(--c-ink); margin-bottom: 0.2rem;">
                                    {{ s.name }}
                                </div>
                                <div style="font-size: 0.74rem; color: var(--c-ink-muted);">
                                    {{ s.project_name }}
                                </div>
                                <div style="margin-top: 0.5rem; text-align: right;">
                                    <Link :href="'/sites/' + s.id" class="btn btn-outline btn-sm" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">
                                        View Dossier →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Site View -->
                    <div v-else>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.84rem;">
                            <div>
                                <span style="color: var(--c-ink-muted); display: block;">Initiative</span>
                                <span style="font-weight: 600; color: var(--c-ink);">{{ activeSelection.site.project_name }}</span>
                            </div>

                            <div>
                                <span style="color: var(--c-ink-muted); display: block;">Current Status</span>
                                <span :class="['badge', activeSelection.site.is_operational ? 'badge-pass' : 'badge-warning']" style="margin-top: 0.25rem;">
                                    {{ activeSelection.site.current_status.replace('_', ' ').toUpperCase() }}
                                </span>
                            </div>

                            <div>
                                <span style="color: var(--c-ink-muted); display: block;">Administrative Location</span>
                                <span>{{ activeSelection.site.barangay }}, {{ activeSelection.site.municipality }}, {{ activeSelection.site.province }}</span>
                            </div>

                            <div>
                                <span style="color: var(--c-ink-muted); display: block;">WGS84 Coordinates</span>
                                <span class="font-mono">
                                    {{ Number(activeSelection.site.lat || 0).toFixed(6) }}, {{ Number(activeSelection.site.lng || 0).toFixed(6) }}
                                </span>
                            </div>

                            <div v-if="activeSelection.site.target_completion_date">
                                <span style="color: var(--c-ink-muted); display: block;">Target Completion</span>
                                <span>{{ activeSelection.site.target_completion_date }}</span>
                            </div>

                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--c-border-subtle);">
                                <Link :href="'/sites/' + activeSelection.site.id" class="btn btn-primary" style="width: 100%;">
                                    <span>Open Full Timeline Dossier</span>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import MapView from '@/Components/Map/MapView.vue';

const props = defineProps({
    initialGeojson: {
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
    summary: {
        type: Object,
        default: () => ({}),
    },
    initialFocus: {
        type: Object,
        default: () => ({}),
    },
});

const selectedMetric = ref('completion');
const selectedBasemap = ref('canvas');
const granularitySlider = ref(50);
const clusterMode = ref('distance');
const showFilterDrawer = ref(false);
const selectedProjectIds = ref(
    props.initialFocus.project_id ? [props.initialFocus.project_id] : props.projects.map(p => p.id)
);

const activeSelection = ref(null);

const activePointsCount = computed(() => {
    if (!selectedProjectIds.value || selectedProjectIds.value.length === 0) {
        return props.initialGeojson.features.length;
    }
    return props.initialGeojson.features.filter(f => selectedProjectIds.value.includes(f.properties.project_id)).length;
});

function getProjectsForKind(kind) {
    return props.projects.filter(p => p.project_type?.kind === kind);
}

function getKindHeading(kind) {
    switch (kind) {
        case 'site': return 'Kind A • Site Deployments';
        case 'institution': return 'Kind B • Institution Adoptions';
        case 'activity': return 'Kind C • Capacity & Beneficiaries';
        case 'milestone': return 'Kind D • Milestones & Studies';
        default: return 'Projects';
    }
}

function getKindBadge(kind) {
    switch (kind) {
        case 'site': return 'badge-kind-a';
        case 'institution': return 'badge-kind-b';
        case 'activity': return 'badge-kind-c';
        case 'milestone': return 'badge-kind-d';
        default: return 'badge-secondary';
    }
}

function selectAllProjects() {
    selectedProjectIds.value = props.projects.map(p => p.id);
}

function clearProjectFilters() {
    selectedProjectIds.value = [];
}

function onClusterSelected(clusterData) {
    activeSelection.value = {
        isCluster: true,
        lat: clusterData.lat,
        lng: clusterData.lng,
        count: clusterData.count,
        percentage: clusterData.percentage,
        name: clusterData.name,
        sites: clusterData.sites,
    };
}

function onSiteSelected(siteProps) {
    activeSelection.value = {
        isCluster: false,
        site: siteProps,
    };
}
</script>

<style>
.map-page-wrapper {
    position: relative;
    width: 100%;
    height: calc(100vh - var(--topbar-height));
    overflow: hidden;
}

/* Floating Top Control Ribbon (NMS-V2 reference design) */
.map-floating-ribbon {
    position: absolute;
    top: 1rem;
    left: 1rem;
    right: 1rem;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    padding: 0.65rem 1rem;
    box-shadow: var(--shadow-md);
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    pointer-events: auto;
}

.ribbon-metric-pill {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.ribbon-label {
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--c-ink-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.ribbon-sublabel {
    font-size: 0.65rem;
    color: var(--c-ink-subtle);
    font-family: var(--font-mono);
}

.ribbon-val {
    font-size: 0.95rem;
    font-weight: 800;
    font-family: var(--font-mono);
    color: var(--c-ink);
}

.ribbon-divider {
    width: 1px;
    height: 28px;
    background: var(--c-border);
}

.ribbon-control-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ribbon-select {
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    color: var(--c-ink);
    font-weight: 500;
}

.slider-group {
    min-width: 180px;
}

.ribbon-slider {
    width: 100%;
    height: 4px;
    background: var(--c-border);
    border-radius: var(--radius-full);
    outline: none;
    cursor: pointer;
    margin-top: 4px;
}

.ribbon-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--c-primary);
    border: 2px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: transform 0.15s ease;
}

.ribbon-slider::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}

.ribbon-tabs {
    padding: 0.15rem;
}

.ribbon-tabs .tab-btn {
    padding: 0.25rem 0.65rem;
    font-size: 0.75rem;
}

.ribbon-filter-btn {
    margin-left: auto;
    font-size: 0.8rem;
    padding: 0.4rem 0.75rem;
}

/* Map Slide-Over Drawers */
.map-drawer {
    position: absolute;
    top: 5rem;
    bottom: 1rem;
    width: 380px;
    max-width: calc(100vw - 2rem);
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: drawer-slide 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes drawer-slide {
    from { transform: translateX(30px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.filter-drawer {
    right: 1rem;
}

.dossier-drawer {
    left: 1rem;
}

.drawer-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--c-border-subtle);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    background: var(--c-surface-subtle);
}

.drawer-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--c-ink);
}

.drawer-subtitle {
    font-size: 0.76rem;
    color: var(--c-ink-muted);
    margin-top: 2px;
}

.drawer-body {
    padding: 1.25rem;
    overflow-y: auto;
    flex: 1;
}

.project-filter-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.65rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.15s ease;
}

.project-filter-item:hover {
    border-color: var(--c-primary-border);
}

.project-filter-item.active {
    background: var(--c-primary-subtle);
    border-color: var(--c-primary);
}

.project-filter-name {
    font-weight: 500;
    color: var(--c-ink);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
