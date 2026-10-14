<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Field Intervention Submission</h1>
                <p class="page-description">
                    Record append-only field engineering reports, telemetry measurements, and photo evidence.
                </p>
            </div>
            <div class="page-actions">
                <Link href="/sites" class="btn btn-secondary">
                    Back to Registry
                </Link>
            </div>
        </div>

        <form @submit.prevent="submitForm" style="max-width: 820px;" enctype="multipart/form-data">
            <!-- 1. Site Selection & Context Card -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin-bottom: 1rem;">1. Target Deployment Site</h2>
                <div class="form-group">
                    <label class="form-label required">Select Enrolled Site</label>
                    <select v-model="form.project_site_id" class="form-control" required @change="onSiteSelected">
                        <option value="">Choose Site</option>
                        <option v-for="s in sites" :key="s.id" :value="s.id">
                            {{ s.site_code }} • {{ s.location?.name || s.institution?.name }} ({{ s.project?.name }})
                        </option>
                    </select>
                </div>

                <!-- Selected Site Telemetry Summary -->
                <div v-if="selectedSite" style="margin-top: 1rem; padding: 1rem; background: var(--c-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--c-border);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <div>
                            <span class="project-code-badge">{{ selectedSite.site_code }}</span>
                            <span style="font-weight: 600; font-size: 0.95rem; margin-left: 0.5rem; color: var(--c-ink);">
                                {{ selectedSite.location?.name || selectedSite.institution?.name }}
                            </span>
                        </div>
                        <span class="badge badge-pass">
                            Current: {{ selectedSite.current_status.replace('_', ' ') }}
                        </span>
                    </div>

                    <div style="font-size: 0.8rem; color: var(--c-ink-muted);">
                        <span>Jurisdiction: {{ selectedSite.location?.barangay?.name || 'Region 2' }}</span>
                        <span v-if="selectedSite.location?.lat" class="font-mono" style="margin-left: 0.85rem;">
                            GPS: {{ Number(selectedSite.location.lat).toFixed(4) }}, {{ Number(selectedSite.location.lng).toFixed(4) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Status Progression & Event -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin-bottom: 1rem;">2. Lifecycle Event & Transition</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label required">Intervention Type</label>
                        <select v-model="form.intervention_type" class="form-control" required>
                            <option value="">Select Event Type</option>
                            <option v-for="inv in availableInterventions" :key="inv.id" :value="inv.key">
                                {{ inv.label }}
                            </option>
                            <option value="routine_inspection">Routine QA / Inspection</option>
                            <option value="speed_test">Speedtest / Latency Benchmark</option>
                            <option value="preventive_maintenance">Preventive Maintenance</option>
                            <option value="restoration">Restoration & Repair</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">New Lifecycle Status</label>
                        <select v-model="form.new_status" class="form-control" required>
                            <option value="">Select Target Status</option>
                            <option v-for="st in availableStatuses" :key="st.id" :value="st.key">
                                {{ st.label }} ({{ st.key }})
                            </option>
                        </select>
                        <span style="font-size: 0.74rem; color: var(--c-ink-muted); margin-top: 0.25rem; display: block;">
                            Allowed transitions validated strictly against state-machine rules.
                        </span>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Occurrence Date & Time</label>
                        <input type="datetime-local" v-model="form.occurred_at" class="form-control font-mono" />
                    </div>
                </div>
            </div>

            <!-- 3. GPS Geolocation Telemetry -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h2 class="card-title" style="margin-bottom: 0;">3. GPS Coordinates & Proximity Check</h2>
                        <p style="font-size: 0.8rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Anti-fraud validation verifies that submission coordinates match the registered site (&lt;500m).
                        </p>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" @click="captureGps" :disabled="isCapturingGps">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>{{ isCapturingGps ? 'Acquiring GPS...' : 'Capture Device GPS' }}</span>
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input
                            type="number"
                            step="0.000001"
                            v-model.number="form.submission_lat"
                            placeholder="e.g. 17.6132"
                            class="form-control font-mono"
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input
                            type="number"
                            step="0.000001"
                            v-model.number="form.submission_lng"
                            placeholder="e.g. 121.7270"
                            class="form-control font-mono"
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accuracy (Meters)</label>
                        <input
                            type="number"
                            step="0.1"
                            v-model.number="form.gps_accuracy_m"
                            placeholder="e.g. 4.5"
                            class="form-control font-mono"
                            readonly
                        />
                    </div>
                </div>

                <div v-if="gpsStatusMessage" style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--c-primary);">
                    {{ gpsStatusMessage }}
                </div>
            </div>

            <!-- 4. Dynamic Custom Attributes & Photos -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin-bottom: 1rem;">4. Telemetry Attributes & Photo Evidence</h2>

                <!-- Custom Fields -->
                <div v-if="customFields.length > 0" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div
                        v-for="field in customFields"
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
                            v-model="form.custom_values[field.key]"
                            class="form-control"
                            :required="field.required"
                        />
                        <select
                            v-else-if="field.type === 'select'"
                            v-model="form.custom_values[field.key]"
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

                <!-- Photos Upload -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Photo Evidence (Acceptance / Speedtest / Hardware)</label>
                    <input
                        type="file"
                        multiple
                        accept="image/*"
                        @change="onPhotosSelected"
                        class="form-control"
                    />
                    <span style="font-size: 0.74rem; color: var(--c-ink-muted); margin-top: 0.25rem; display: block;">
                        Photos will be inspected for SHA-256 duplicate image hashes and embedded EXIF GPS timestamps.
                    </span>
                </div>

                <!-- Notes / Field Remarks -->
                <div class="form-group">
                    <label class="form-label">Field Engineer Notes & Remarks</label>
                    <textarea
                        v-model="form.notes"
                        rows="4"
                        placeholder="Detail installation parameters, equipment serial numbers, speedtest results, or issues encountered..."
                        class="form-control"
                    ></textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <Link href="/sites" class="btn btn-secondary">Cancel</Link>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    Commit Append-Only Report
                </button>
            </div>
        </form>
    </AppShell>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    sites: {
        type: Array,
        default: () => [],
    },
    initialSiteId: {
        type: Number,
        default: null,
    },
});

const isCapturingGps = ref(false);
const gpsStatusMessage = ref('');

const form = useForm({
    project_site_id: props.initialSiteId || '',
    intervention_type: 'qa_inspection',
    new_status: '',
    occurred_at: new Date().toISOString().slice(0, 16),
    notes: '',
    submission_lat: null,
    submission_lng: null,
    gps_accuracy_m: null,
    custom_values: {},
    photos: [],
});

const selectedSite = computed(() => {
    return props.sites.find(s => s.id === form.project_site_id);
});

const availableInterventions = computed(() => {
    return selectedSite.value?.project?.project_type?.intervention_types || [];
});

const availableStatuses = computed(() => {
    return selectedSite.value?.project?.project_type?.status_defs || [];
});

const customFields = computed(() => {
    return selectedSite.value?.project?.project_type?.field_defs || [];
});

function onSiteSelected() {
    form.custom_values = {};
    if (selectedSite.value) {
        form.new_status = selectedSite.value.current_status;
        // Autofill registered GPS as baseline
        if (selectedSite.value.location?.lat && !form.submission_lat) {
            form.submission_lat = Number(selectedSite.value.location.lat);
            form.submission_lng = Number(selectedSite.value.location.lng);
        }
    }
}

function captureGps() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }

    isCapturingGps.value = true;
    gpsStatusMessage.value = 'Locating device via WGS84 GPS...';

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.submission_lat = parseFloat(pos.coords.latitude.toFixed(6));
            form.submission_lng = parseFloat(pos.coords.longitude.toFixed(6));
            form.gps_accuracy_m = parseFloat(pos.coords.accuracy.toFixed(1));
            isCapturingGps.value = false;
            gpsStatusMessage.value = `GPS Acquired (±${form.gps_accuracy_m}m accuracy).`;
        },
        (err) => {
            isCapturingGps.value = false;
            gpsStatusMessage.value = `GPS acquisition failed: ${err.message}. Using fallback.`;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function onPhotosSelected(event) {
    form.photos = Array.from(event.target.files);
}

function submitForm() {
    form.post('/submissions', {
        forceFormData: true,
    });
}

onMounted(() => {
    if (form.project_site_id) {
        onSiteSelected();
    }
});
</script>
