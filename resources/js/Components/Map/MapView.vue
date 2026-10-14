<template>
    <div class="map-view-container" style="position: relative; width: 100%; height: 100%;">
        <div ref="mapElement" class="leaflet-canvas" style="width: 100%; height: 100%; z-index: 1;"></div>

        <!-- Metric Legend Badge (NMS Reference) -->
        <div class="map-legend-card">
            <div class="legend-title">Metric: {{ activeMetricLabel }}</div>
            <div class="legend-scale">
                <span class="legend-bar" style="background: linear-gradient(90deg, #ef4444 0%, #f59e0b 55%, #10b981 100%);"></span>
                <div class="legend-labels">
                    <span>≤20%</span>
                    <span>50%</span>
                    <span>≥95%</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Supercluster from 'supercluster';
import { createClusterIcon, createSiteIcon, getPercentageColor } from './MapUtils';

const props = defineProps({
    features: {
        type: Array,
        default: () => [],
    },
    metric: {
        type: String,
        default: 'completion', // 'completion' | 'operational'
    },
    granularity: {
        type: Number,
        default: 50, // Supercluster radius
    },
    clusterMode: {
        type: String,
        default: 'distance', // 'distance' | 'admin'
    },
    provinces: {
        type: Array,
        default: () => [],
    },
    selectedProjectIds: {
        type: Array,
        default: () => [],
    },
    focusCoords: {
        type: Object,
        default: () => ({}),
    },
    basemap: {
        type: String,
        default: 'canvas', // 'canvas' | 'osm' | 'satellite'
    },
});

const emit = defineEmits(['select-cluster', 'select-site']);

const mapElement = ref(null);
let map = null;
let tileLayer = null;
let clusterLayerGroup = null;
let polygonLayerGroup = null;
let superclusterIndex = null;
let themeObserver = null;

const activeMetricLabel = computed(() => {
    return props.metric === 'operational' ? 'Operational Rate %' : 'Completion Rate %';
});

// Filter features based on selected projects
const filteredFeatures = computed(() => {
    if (!props.selectedProjectIds || props.selectedProjectIds.length === 0) {
        return props.features;
    }
    return props.features.filter(f => props.selectedProjectIds.includes(f.properties.project_id));
});

function initMap() {
    if (!mapElement.value) return;

    // Region 2 Initial Centroid
    const initialLat = props.focusCoords.lat || 17.6132;
    const initialLng = props.focusCoords.lng || 121.7270;
    const initialZoom = props.focusCoords.zoom || 8;

    map = L.map(mapElement.value, {
        center: [initialLat, initialLng],
        zoom: initialZoom,
        minZoom: 6,
        maxZoom: 18,
        zoomControl: false,
    });

    // Custom Zoom Control top-right
    L.control.zoom({ position: 'topright' }).addTo(map);

    clusterLayerGroup = L.layerGroup().addTo(map);
    polygonLayerGroup = L.layerGroup().addTo(map);

    updateBasemap();

    // Watch theme changes on <html> attribute
    themeObserver = new MutationObserver(() => {
        updateBasemap();
    });
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    // Event listeners
    map.on('moveend', () => {
        renderClusters();
    });

    // Load choropleth province polygons
    loadChoroplethPolygons();

    // Build cluster index
    rebuildClusterIndex();
}

function updateBasemap() {
    if (!map) return;
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    let tileUrl = '';
    let attribution = '';
    let maxNativeZoom = 19;

    if (props.basemap === 'osm') {
        tileUrl = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors';
        maxNativeZoom = 19;
    } else if (props.basemap === 'satellite') {
        tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
        attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
        maxNativeZoom = 19;
    } else {
        // Default: Clean Minimalist Canvas (World Light/Dark Gray Base) - Zero Watermarks!
        tileUrl = isDark
            ? 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}'
            : 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}';
        attribution = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ';
        maxNativeZoom = 16;
    }

    if (tileLayer) {
        map.removeLayer(tileLayer);
    }

    tileLayer = L.tileLayer(tileUrl, {
        attribution: attribution,
        maxNativeZoom: maxNativeZoom,
        maxZoom: 19,
    }).addTo(map);
}

function loadChoroplethPolygons() {
    fetch('/geo/r2-provinces.json')
        .then(res => res.json())
        .then(data => {
            if (!polygonLayerGroup || !map) return;
            polygonLayerGroup.clearLayers();

            L.geoJSON(data, {
                style: () => ({
                    color: 'var(--c-primary)',
                    weight: 1.5,
                    opacity: 0.6,
                    fillColor: 'var(--c-primary)',
                    fillOpacity: 0.04,
                    dashArray: '3, 4',
                }),
            }).addTo(polygonLayerGroup);
        })
        .catch(() => {});
}

function rebuildClusterIndex() {
    if (!filteredFeatures.value) return;

    superclusterIndex = new Supercluster({
        radius: props.granularity,
        maxZoom: 16,
        map: (props) => ({
            count: 1,
            completed_count: props.is_completed ? 1 : 0,
            operational_count: props.is_operational ? 1 : 0,
            site_ids: [props.id],
        }),
        reduce: (accum, props) => {
            accum.count += props.count;
            accum.completed_count += props.completed_count;
            accum.operational_count += props.operational_count;
            accum.site_ids = accum.site_ids.concat(props.site_ids);
        },
    });

    superclusterIndex.load(filteredFeatures.value);
    renderClusters();
}

function renderClusters() {
    if (!map || !clusterLayerGroup) return;
    clusterLayerGroup.clearLayers();

    if (props.clusterMode === 'admin') {
        renderAdminRollup();
        return;
    }

    if (!superclusterIndex) return;

    const bounds = map.getBounds();
    const bbox = [bounds.getWest(), bounds.getSouth(), bounds.getEast(), bounds.getNorth()];
    const zoom = Math.floor(map.getZoom());

    const clusters = superclusterIndex.getClusters(bbox, zoom);

    clusters.forEach(cluster => {
        const [lng, lat] = cluster.geometry.coordinates;
        const isCluster = cluster.properties.cluster;

        if (isCluster) {
            const count = cluster.properties.point_count;
            const completed = cluster.properties.completed_count || 0;
            const operational = cluster.properties.operational_count || 0;

            const percentage = props.metric === 'operational'
                ? (count > 0 ? (operational / count) * 100 : 0)
                : (count > 0 ? (completed / count) * 100 : 0);

            const icon = createClusterIcon(L, count, percentage);
            const marker = L.marker([lat, lng], { icon: icon });

            marker.on('click', () => {
                const leaves = superclusterIndex.getLeaves(cluster.id, Infinity);
                emit('select-cluster', {
                    lat,
                    lng,
                    count,
                    percentage,
                    metric: props.metric,
                    sites: leaves.map(l => l.properties),
                });

                // Smooth fly-to cluster expansion
                const expansionZoom = superclusterIndex.getClusterExpansionZoom(cluster.id);
                map.flyTo([lat, lng], expansionZoom, { duration: 0.6 });
            });

            clusterLayerGroup.addLayer(marker);
        } else {
            // Individual Site Pin
            const siteProps = cluster.properties;
            const isOperational = siteProps.is_operational;
            const icon = createSiteIcon(L, siteProps.marker_shape, siteProps.project_color, isOperational);
            const marker = L.marker([lat, lng], { icon: icon });

            marker.on('click', () => {
                emit('select-site', siteProps);
            });

            clusterLayerGroup.addLayer(marker);
        }
    });
}

function renderAdminRollup() {
    // Mode 2: Administrative Rollup by Province Centroid
    const zoom = map.getZoom();

    props.provinces.forEach(prov => {
        if (!prov.centroid_lat || !prov.centroid_lng) return;

        const provSites = filteredFeatures.value.filter(f => {
            const psgc = f.properties.psgc_code || '';
            return psgc.startsWith(prov.code.slice(0, 4));
        });

        const count = provSites.length;
        if (count === 0) return;

        const completed = provSites.filter(s => s.properties.is_completed).length;
        const operational = provSites.filter(s => s.properties.is_operational).length;

        const percentage = props.metric === 'operational'
            ? (operational / count) * 100
            : (completed / count) * 100;

        const icon = createClusterIcon(L, count, percentage);
        const marker = L.marker([prov.centroid_lat, prov.centroid_lng], { icon: icon });

        marker.on('click', () => {
            emit('select-cluster', {
                lat: prov.centroid_lat,
                lng: prov.centroid_lng,
                count,
                percentage,
                name: prov.name,
                sites: provSites.map(s => s.properties),
            });
            map.flyTo([prov.centroid_lat, prov.centroid_lng], 10, { duration: 0.8 });
        });

        clusterLayerGroup.addLayer(marker);
    });
}

// Watchers
watch(() => props.features, () => rebuildClusterIndex(), { deep: true });
watch(() => props.selectedProjectIds, () => rebuildClusterIndex(), { deep: true });
watch(() => props.granularity, () => rebuildClusterIndex());
watch(() => props.metric, () => renderClusters());
watch(() => props.clusterMode, () => renderClusters());
watch(() => props.basemap, () => updateBasemap());

onMounted(() => {
    initMap();
});

onBeforeUnmount(() => {
    if (themeObserver) {
        themeObserver.disconnect();
    }
    if (map) {
        map.remove();
    }
});
</script>

<style>
/* Bubble Cluster Marker Styles */
.custom-cluster-wrapper,
.custom-site-wrapper {
    background: transparent;
    border: none;
}

.nms-cluster-bubble {
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-family: var(--font-sans);
    cursor: pointer;
    transition: transform 0.16s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.16s ease;
    user-select: none;
    line-height: 1;
}

.nms-cluster-bubble:hover {
    transform: scale(1.18);
    box-shadow: 0 0 0 3px #ffffff, 0 4px 14px rgba(0, 0, 0, 0.45) !important;
    z-index: 9999 !important;
}

.bubble-count {
    font-weight: 800;
    font-size: 0.74rem;
    line-height: 1;
    letter-spacing: -0.02em;
}

.bubble-pct {
    font-size: 0.54rem;
    font-weight: 700;
    line-height: 1;
    margin-top: 1px;
    opacity: 0.95;
    font-family: var(--font-mono);
}

.nms-site-marker {
    cursor: pointer;
    transition: transform 0.15s ease;
}

.nms-site-marker:hover {
    transform: scale(1.5) !important;
    z-index: 9999 !important;
}

/* Floating Map Legend Card */
.map-legend-card {
    position: absolute;
    bottom: 1.5rem;
    left: 1.5rem;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-md);
    padding: 0.65rem 0.85rem;
    box-shadow: var(--shadow-md);
    z-index: 1000;
    pointer-events: auto;
    min-width: 170px;
}

.legend-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--c-ink-muted);
    margin-bottom: 0.35rem;
}

.legend-scale {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.legend-bar {
    display: block;
    height: 7px;
    border-radius: var(--radius-full);
    width: 100%;
}

.legend-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.68rem;
    font-family: var(--font-mono);
    color: var(--c-ink-subtle);
    font-weight: 600;
}
</style>
