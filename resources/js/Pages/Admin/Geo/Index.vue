<template>
    <AppShell>
        <div class="page-header">
            <div class="page-title-group">
                <h1>Philippine Administrative Hierarchy & Districts</h1>
                <div class="page-subtitle">
                    Region 2 PSGC boundaries, 12 Congressional Districts, and municipal coverage tree
                </div>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <span class="chip-status status-online">
                    <span class="status-dot"></span>
                    <span>PSGC Release 2026Q2 Verified</span>
                </span>
            </div>
        </div>

        <!-- Stat Row -->
        <div class="grid-4" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Provinces</span>
                    <span class="badge badge-kind-a">Region II</span>
                </div>
                <div class="metric-value">{{ stats.provinces }}</div>
                <div class="metric-subtext">Cagayan Valley Jurisdiction</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Congressional Districts</span>
                    <span class="badge badge-kind-b">Legislative</span>
                </div>
                <div class="metric-value">{{ stats.districts }}</div>
                <div class="metric-subtext">Versioned Apportionment</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Cities & Municipalities</span>
                    <span class="badge badge-kind-c">LGUs</span>
                </div>
                <div class="metric-value">{{ stats.cities_municipalities }}</div>
                <div class="metric-subtext">1 ICC • 3 CC • 87 Mun</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Barangays Tracked</span>
                    <span class="badge badge-kind-d">Centroids</span>
                </div>
                <div class="metric-value">{{ stats.barangays }}</div>
                <div class="metric-subtext">100% Geocoded</div>
            </div>
        </div>

        <!-- Split Grid: Explorer Tree on Left, Leaflet Preview on Right -->
        <div class="grid-split-2-1" style="align-items: start;">
            <!-- Left: Administrative Hierarchy Tree -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Region 02 Administrative Tree</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Click a province or district to highlight and center the map
                        </div>
                    </div>
                </div>
                <div class="card-body" style="padding: 1rem;">
                    <!-- Province Accordions -->
                    <div v-for="prov in provinces" :key="prov.code" style="margin-bottom: 0.85rem; border: 1px solid var(--c-border); border-radius: var(--radius-md); overflow: hidden;">
                        <!-- Province Header -->
                        <div 
                            @click="toggleProvince(prov)"
                            style="padding: 0.85rem 1rem; background: var(--c-surface-subtle); display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;"
                        >
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <span style="font-size: 0.85rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                                    {{ expandedProvinces.includes(prov.code) ? '▼' : '▶' }}
                                </span>
                                <div>
                                    <strong style="font-size: 0.95rem; color: var(--c-ink);">{{ prov.name }}</strong>
                                    <span style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--c-ink-muted); margin-left: 0.5rem;">
                                        PSGC: {{ prov.code }}
                                    </span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge badge-kind-b">{{ prov.districts_count }} Districts</span>
                                <span class="badge badge-kind-c">{{ prov.cities_count }} LGUs</span>
                            </div>
                        </div>

                        <!-- Districts inside Province -->
                        <div v-if="expandedProvinces.includes(prov.code)" style="padding: 0.75rem 1rem; background: var(--c-surface);">
                            <div v-for="dist in prov.districts" :key="dist.id" style="margin-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px dashed var(--c-border-subtle);">
                                    <span style="font-weight: 600; font-size: 0.85rem; color: var(--c-primary);">
                                        🏛️ {{ dist.name }}
                                    </span>
                                    <span style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                                        {{ dist.lgus_count }} Municipalities/Cities
                                    </span>
                                </div>

                                <!-- LGUs pills -->
                                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.5rem 0;">
                                    <button 
                                        v-for="lgu in dist.lgus" 
                                        :key="lgu.code"
                                        class="btn btn-secondary btn-sm"
                                        style="font-size: 0.78rem;"
                                        @click="selectLgu(lgu, prov)"
                                    >
                                        <span>{{ lgu.name }}</span>
                                        <span v-if="lgu.city_class" class="badge badge-kind-a" style="font-size: 0.65rem; padding: 0.1rem 0.35rem;">{{ lgu.city_class }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Interactive Leaflet Boundary Viewer -->
            <div class="card" style="position: sticky; top: calc(var(--topbar-height) + 1.5rem);">
                <div class="card-header">
                    <div>
                        <div class="card-title">Boundary & Coordinate Viewer</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            {{ activeSelection ? activeSelection.name : 'Region 2 (Cagayan Valley)' }}
                        </div>
                    </div>
                    <button class="btn btn-ghost btn-sm" @click="resetMapView">Reset View</button>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div id="geo-map" style="height: 480px; width: 100%; border-bottom-left-radius: var(--radius-lg); border-bottom-right-radius: var(--radius-lg);"></div>
                </div>
                <div class="card-footer" v-if="activeSelection">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.8rem; font-family: var(--font-mono);">
                            Selected: <strong>{{ activeSelection.name }}</strong> ({{ activeSelection.code }})
                        </span>
                        <button v-if="activeSelection.level !== 'prov'" class="btn btn-outline btn-sm" @click="fetchBarangays(activeSelection.code)">
                            View Barangays
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Barangays of Selected LGU -->
        <div v-if="showingBarangaysFor" class="modal-backdrop" @click.self="showingBarangaysFor = null">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="card-title">Barangays in {{ showingBarangaysFor.name }}</h3>
                    <button class="btn btn-ghost btn-sm" @click="showingBarangaysFor = null">✕</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div v-if="loadingBarangays" style="text-align: center; padding: 2rem; color: var(--c-ink-muted);">
                        Loading barangay centroids...
                    </div>
                    <div v-else class="grid-2">
                        <div v-for="b in lguBarangays" :key="b.code" style="padding: 0.5rem 0.75rem; border: 1px solid var(--c-border); border-radius: var(--radius-sm); background: var(--c-surface-subtle);">
                            <div style="font-weight: 600; font-size: 0.85rem;">{{ b.name }}</div>
                            <div style="font-size: 0.72rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                                PSGC: {{ b.code }} • Lat: {{ b.centroid_lat }} Lng: {{ b.centroid_lng }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="showingBarangaysFor = null">Close</button>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    provinces: Array,
    stats: Object,
});

const expandedProvinces = ref(['0215000000', '0231000000']);
const activeSelection = ref(null);
const showingBarangaysFor = ref(null);
const lguBarangays = ref([]);
const loadingBarangays = ref(false);

let map = null;
let geojsonLayer = null;
let markerLayer = null;

function toggleProvince(prov) {
    const idx = expandedProvinces.value.indexOf(prov.code);
    if (idx > -1) {
        expandedProvinces.value.splice(idx, 1);
    } else {
        expandedProvinces.value.push(prov.code);
    }
    selectEntity(prov.name, prov.code, 'prov', prov.lat, prov.lng, 9);
}

function selectLgu(lgu, prov) {
    selectEntity(lgu.name, lgu.code, lgu.level, lgu.lat, lgu.lng, 12);
}

function selectEntity(name, code, level, lat, lng, zoom = 10) {
    activeSelection.value = { name, code, level, lat, lng };

    if (map && lat && lng) {
        map.flyTo([lat, lng], zoom, { duration: 1.2 });

        if (markerLayer) {
            markerLayer.clearLayers();
            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: '#1d4ed8',
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9,
            }).bindPopup(`<strong>${name}</strong><br>PSGC: ${code}`).openPopup();
            markerLayer.addLayer(marker);
        }
    }
}

function resetMapView() {
    activeSelection.value = null;
    if (map) {
        map.flyTo([17.6131, 121.7269], 7, { duration: 1 });
        if (markerLayer) markerLayer.clearLayers();
    }
}

async function fetchBarangays(citymunCode) {
    showingBarangaysFor.value = activeSelection.value;
    loadingBarangays.value = true;
    try {
        const res = await axios.get(`/admin/geo/barangays/${citymunCode}`);
        lguBarangays.value = res.data;
    } catch (err) {
        console.error(err);
    } finally {
        loadingBarangays.value = false;
    }
}

onMounted(() => {
    // Initialize Leaflet Map with CARTO Positron Light Tiles (matching NMS-V2)
    map = L.map('geo-map', {
        center: [17.6131, 121.7269],
        zoom: 7,
        zoomControl: true,
    });

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
        maxNativeZoom: 16,
        maxZoom: 19,
    }).addTo(map);

    markerLayer = L.layerGroup().addTo(map);

    // Load Province GeoJSON Boundaries
    fetch('/geo/r2-provinces.json')
        .then(res => res.json())
        .then(data => {
            geojsonLayer = L.geoJSON(data, {
                style: {
                    color: '#1d4ed8',
                    weight: 1.5,
                    fillColor: '#3b82f6',
                    fillOpacity: 0.08,
                },
                onEachFeature: (feature, layer) => {
                    layer.bindTooltip(feature.properties.name, { permanent: false, direction: 'center' });
                    layer.on('click', () => {
                        const bounds = layer.getBounds();
                        map.fitBounds(bounds);
                    });
                }
            }).addTo(map);
        })
        .catch(err => console.error('Failed to load GeoJSON boundaries:', err));
});
</script>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    padding: 1rem;
}

.modal-container {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 600px;
}

.modal-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--c-border-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-body {
    padding: 1.25rem;
}

.modal-footer {
    padding: 0.85rem 1.25rem;
    border-top: 1px solid var(--c-border-subtle);
    background: var(--c-surface-subtle);
    border-bottom-left-radius: var(--radius-lg);
    border-bottom-right-radius: var(--radius-lg);
    display: flex;
    justify-content: flex-end;
}
</style>
