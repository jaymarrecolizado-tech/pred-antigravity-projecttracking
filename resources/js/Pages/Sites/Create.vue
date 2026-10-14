<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Enroll New Site</h1>
                <p class="page-description">
                    Bind a physical location or institutional asset to an active initiative and initialize tracking telemetry.
                </p>
            </div>
            <div class="page-actions">
                <Link href="/sites" class="btn btn-secondary">
                    Back to Registry
                </Link>
            </div>
        </div>

        <form @submit.prevent="submitForm" style="max-width: 860px;">
            <!-- Section 1: Initiative Selection -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin-bottom: 1rem;">1. Select Target Project</h2>
                <div class="form-group">
                    <label class="form-label required">Active Initiative</label>
                    <select v-model="form.project_id" class="form-control" required @change="onProjectSelect">
                        <option value="">Select Project</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">
                            {{ p.code }} • {{ p.name }} ({{ p.project_type?.kind?.toUpperCase() }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Section 2: Location Anchor (Existing vs New) -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 class="card-title" style="margin-bottom: 0;">2. Physical Location Anchor</h2>
                    <div class="tabs-nav">
                        <button
                            type="button"
                            class="tab-btn"
                            :class="{ active: !form.use_existing_location }"
                            @click="form.use_existing_location = false"
                        >
                            Register New Location
                        </button>
                        <button
                            type="button"
                            class="tab-btn"
                            :class="{ active: form.use_existing_location }"
                            @click="form.use_existing_location = true"
                        >
                            Link Existing Location
                        </button>
                    </div>
                </div>

                <!-- Option A: Link Existing Location -->
                <div v-if="form.use_existing_location">
                    <div class="form-group">
                        <label class="form-label required">Select Existing Physical Location</label>
                        <select v-model="form.location_id" class="form-control" required>
                            <option value="">Choose Location</option>
                            <option v-for="loc in existingLocations" :key="loc.id" :value="loc.id">
                                {{ loc.name }} ({{ loc.category }}) • [{{ Number(loc.lat).toFixed(4) }}, {{ Number(loc.lng).toFixed(4) }}]
                            </option>
                        </select>
                        <span style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.35rem; display: block;">
                            Shared coordinates prevent duplication when multiple programs co-locate at the same school, plaza, or LGU hall.
                        </span>
                    </div>
                </div>

                <!-- Option B: Register New Location -->
                <div v-else>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label required">Location Name</label>
                            <input
                                type="text"
                                v-model="form.name"
                                placeholder="e.g. San Mariano Municipal Plaza"
                                class="form-control"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Facility Category</label>
                            <select v-model="form.category" class="form-control" required>
                                <option value="plaza">Public Plaza / Park</option>
                                <option value="lgu_hall">LGU Hall / Gov Center</option>
                                <option value="school">Public School / SUC</option>
                                <option value="hospital_clinic">Hospital / RHU Clinic</option>
                                <option value="public_market">Public Market</option>
                                <option value="terminal">Transport Terminal</option>
                                <option value="other">Other Public Facility</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Province</label>
                            <select v-model="selectedProvinceCode" class="form-control" required @change="onProvinceChange">
                                <option value="">Select Province</option>
                                <option v-for="prov in provinces" :key="prov.code" :value="prov.code">
                                    {{ prov.name }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Municipality / City</label>
                            <select v-model="selectedMunCode" class="form-control" required @change="onMunChange">
                                <option value="">Select Municipality</option>
                                <option v-for="mun in filteredMunicipalities" :key="mun.code" :value="mun.code">
                                    {{ mun.name }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Barangay (PSGC 10-char Code)</label>
                            <input
                                type="text"
                                v-model="form.psgc_barangay"
                                placeholder="e.g. 021528001"
                                class="form-control font-mono"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Latitude (WGS84)</label>
                            <input
                                type="number"
                                step="0.000001"
                                v-model.number="form.lat"
                                placeholder="e.g. 17.6132"
                                class="form-control font-mono"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Longitude (WGS84)</label>
                            <input
                                type="number"
                                step="0.000001"
                                v-model.number="form.lng"
                                placeholder="e.g. 121.7270"
                                class="form-control font-mono"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Elevation (Meters ASL)</label>
                            <input
                                type="number"
                                step="0.1"
                                v-model.number="form.elevation_m"
                                placeholder="e.g. 24.5"
                                class="form-control font-mono"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Status & Dynamic Custom Fields -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin-bottom: 1rem;">3. Telemetry Attributes & Lifecycle</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Initial Status</label>
                        <select v-model="form.current_status" class="form-control">
                            <option value="">Default (First Pipeline Stage)</option>
                            <option v-for="s in currentProjectStatuses" :key="s.id" :value="s.key">
                                {{ s.label }} ({{ s.key }})
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Completion Date</label>
                        <input type="date" v-model="form.target_completion_date" class="form-control" />
                    </div>

                    <!-- Dynamic Fields based on Project Type -->
                    <div
                        v-for="field in currentProjectFields"
                        :key="field.id"
                        class="form-group"
                        :style="{ gridColumn: field.type === 'text' ? 'span 2' : 'span 1' }"
                    >
                        <label class="form-label" :class="{ required: field.required }">
                            {{ field.label }}
                        </label>
                        <input
                            v-if="['text', 'number', 'integer'].includes(field.type)"
                            :type="field.type === 'text' ? 'text' : 'number'"
                            v-model="form.custom_attributes[field.key]"
                            class="form-control"
                            :required="field.required"
                        />
                        <select
                            v-else-if="field.type === 'select'"
                            v-model="form.custom_attributes[field.key]"
                            class="form-control"
                            :required="field.required"
                        >
                            <option value="">Select option</option>
                            <option v-for="opt in field.options || []" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <Link href="/sites" class="btn btn-secondary">Cancel</Link>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    Commit Site Enrollment
                </button>
            </div>
        </form>
    </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
    existingLocations: {
        type: Array,
        default: () => [],
    },
    provinces: {
        type: Array,
        default: () => [],
    },
    municipalities: {
        type: Array,
        default: () => [],
    },
});

const selectedProvinceCode = ref('');
const selectedMunCode = ref('');

const filteredMunicipalities = computed(() => {
    if (!selectedProvinceCode.value) return [];
    return props.municipalities.filter(m => m.parent_code === selectedProvinceCode.value);
});

function onProvinceChange() {
    selectedMunCode.value = '';
}

function onMunChange() {
    if (selectedMunCode.value) {
        // Default barangay prefix (first barangay)
        form.psgc_barangay = selectedMunCode.value.slice(0, 7) + '001';
    }
}

const form = useForm({
    project_id: '',
    use_existing_location: false,
    location_id: '',
    name: '',
    category: 'plaza',
    psgc_barangay: '',
    lat: '',
    lng: '',
    elevation_m: '',
    current_status: '',
    target_completion_date: '',
    custom_attributes: {},
});

const selectedProject = computed(() => {
    return props.projects.find(p => p.id === form.project_id);
});

const currentProjectStatuses = computed(() => {
    return selectedProject.value?.project_type?.status_defs || [];
});

const currentProjectFields = computed(() => {
    return selectedProject.value?.project_type?.field_defs || [];
});

function onProjectSelect() {
    form.custom_attributes = {};
}

function submitForm() {
    form.post('/sites');
}
</script>
