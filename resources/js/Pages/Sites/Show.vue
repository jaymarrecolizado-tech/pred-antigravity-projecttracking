<template>
    <AppShell>
        <div class="page-header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span class="project-code-badge">{{ site.site_code }}</span>
                    <span :class="['badge', getStatusBadgeClass(site.current_status)]">
                        {{ site.current_status.replace('_', ' ') }}
                    </span>
                    <span v-if="site.location?.category" class="badge" style="background: var(--c-surface-subtle); border-color: var(--c-border);">
                        {{ site.location.category.toUpperCase() }}
                    </span>
                </div>
                <h1 class="page-title">{{ site.location?.name || site.institution?.name }}</h1>
                <p class="page-description">
                    {{ site.location?.barangay?.name }}, {{ site.location?.barangay?.parent?.name }}, {{ site.location?.barangay?.parent?.parent?.name }} • {{ site.project?.name }}
                </p>
            </div>
            <div class="page-actions">
                <Link
                    v-if="site.location?.lat"
                    :href="'/map?lat=' + site.location.lat + '&lng=' + site.location.lng + '&zoom=15'"
                    class="btn btn-secondary"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon>
                        <line x1="9" x2="9" y1="3" y2="18"></line>
                        <line x1="15" x2="15" y1="6" y2="21"></line>
                    </svg>
                    <span>View on Map</span>
                </Link>
                <Link
                    v-if="canSubmit"
                    :href="'/submissions/create?site_id=' + site.id"
                    class="btn btn-primary"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Submit New Report</span>
                </Link>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start;">
            <!-- Left Column: Site Specification Card -->
            <div class="card" style="padding: 1.25rem;">
                <h2 class="card-title" style="margin-bottom: 0.85rem; font-size: 0.95rem;">Site Telemetry Profile</h2>

                <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.82rem;">
                    <div>
                        <span style="color: var(--c-ink-muted); display: block;">Initiative</span>
                        <span style="font-weight: 600; color: var(--c-ink);">{{ site.project?.name }}</span>
                    </div>

                    <div>
                        <span style="color: var(--c-ink-muted); display: block;">Coordinates (WGS84)</span>
                        <span class="font-mono" style="font-weight: 600;">
                            {{ site.location?.lat ? Number(site.location.lat).toFixed(6) + ', ' + Number(site.location.lng).toFixed(6) : 'None' }}
                        </span>
                        <span v-if="site.location?.elevation_m" style="color: var(--c-ink-subtle); display: block; font-size: 0.74rem;">
                            {{ site.location.elevation_m }}m Elevation
                        </span>
                    </div>

                    <div>
                        <span style="color: var(--c-ink-muted); display: block;">Target Completion</span>
                        <span style="font-weight: 600;">{{ site.target_completion_date || 'No deadline specified' }}</span>
                    </div>

                    <div>
                        <span style="color: var(--c-ink-muted); display: block;">Enrolled Timestamp</span>
                        <span style="font-family: var(--font-mono); font-size: 0.76rem;">{{ formatDate(site.enrolled_at) }}</span>
                    </div>

                    <div v-if="site.custom_attributes && Object.keys(site.custom_attributes).length > 0" style="padding-top: 0.75rem; border-top: 1px dashed var(--c-border);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--c-ink-subtle); margin-bottom: 0.45rem;">
                            Configured Attributes
                        </div>
                        <div v-for="(val, k) in site.custom_attributes" :key="k" style="margin-bottom: 0.35rem;">
                            <span style="color: var(--c-ink-muted); font-size: 0.76rem;">{{ formatKeyLabel(k) }}:</span>
                            <span style="font-weight: 600; margin-left: 0.35rem; color: var(--c-ink);">
                                {{ Array.isArray(val) ? val.join(', ') : val }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Append-Only Chronological Timeline Dossier -->
            <div>
                <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <div>
                            <h2 class="card-title" style="margin-bottom: 0;">Append-Only History & Intervention Timeline</h2>
                            <p style="font-size: 0.8rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                                Tamper-evident chronological record of physical installations, speed tests, and QA verifications.
                            </p>
                        </div>
                        <span class="badge badge-secondary font-mono">
                            {{ interventions.length }} Events Logged
                        </span>
                    </div>

                    <div v-if="interventions.length > 0" class="timeline-container">
                        <div v-for="inv in interventions" :key="inv.id" class="timeline-item">
                            <!-- Node icon on the vertical rail -->
                            <div
                                class="timeline-node"
                                :class="{
                                    pass: inv.field_report?.verification_status === 'verified',
                                    warning: inv.field_report?.verification_status === 'flagged',
                                }"
                            ></div>

                            <div class="timeline-card">
                                <div class="timeline-header">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        <span style="font-weight: 700; color: var(--c-ink); font-size: 0.95rem;">
                                            {{ formatInterventionTitle(inv.intervention_type) }}
                                        </span>
                                        <span class="badge" style="background: var(--c-surface-subtle); border-color: var(--c-border); font-size: 0.7rem;">
                                            {{ inv.previous_status || 'Initial' }} → {{ inv.new_status }}
                                        </span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.45rem;">
                                        <span :class="['badge', getVerificationBadge(inv.field_report?.verification_status)]">
                                            {{ inv.field_report?.verification_status || 'Unverified' }}
                                        </span>
                                        <span class="font-mono" style="font-size: 0.74rem; color: var(--c-ink-muted);">
                                            {{ formatDate(inv.occurred_at) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Verification Warning Flags -->
                                <div v-if="inv.field_report?.verification_flags && inv.field_report.verification_flags.length > 0" style="margin-bottom: 0.65rem; display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                    <span v-for="fl in inv.field_report.verification_flags" :key="fl" class="flag-chip">
                                        ⚠ {{ fl }}
                                    </span>
                                </div>

                                <!-- Field Notes -->
                                <p v-if="inv.field_report?.notes" style="font-size: 0.86rem; color: var(--c-ink); line-height: 1.45; margin: 0.5rem 0;">
                                    {{ inv.field_report.notes }}
                                </p>

                                <!-- Telemetry & Custom Field Badges -->
                                <div v-if="inv.field_report?.custom_values && Object.keys(inv.field_report.custom_values).length > 0" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                    <span v-for="(v, k) in inv.field_report.custom_values" :key="k" class="scope-chip" style="font-size: 0.74rem;">
                                        <strong>{{ formatKeyLabel(k) }}:</strong> {{ Array.isArray(v) ? v.join(', ') : v }}
                                    </span>
                                </div>

                                <!-- Photo Attachments Gallery -->
                                <div v-if="inv.field_report?.attachments && inv.field_report.attachments.length > 0" class="evidence-gallery">
                                    <a
                                        v-for="att in inv.field_report.attachments"
                                        :key="att.id"
                                        :href="att.file_path"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        title="Click to view full photo evidence"
                                    >
                                        <img :src="att.file_path" :alt="att.file_name" class="evidence-thumb" />
                                    </a>
                                </div>

                                <!-- Metadata Footer -->
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; pt: 0.5rem; border-top: 1px solid var(--c-border-subtle); font-size: 0.76rem; color: var(--c-ink-muted);">
                                    <span>Recorded by {{ inv.author?.name || 'Field Personnel' }} via {{ inv.source }}</span>
                                    <span v-if="inv.field_report?.verifier">
                                        Verified by {{ inv.field_report.verifier.name }} on {{ formatDate(inv.field_report.verified_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else style="padding: 3rem 1rem; text-align: center; color: var(--c-ink-muted);">
                        No field interventions logged yet for this site.
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    site: {
        type: Object,
        required: true,
    },
    interventions: {
        type: Array,
        default: () => [],
    },
    canSubmit: {
        type: Boolean,
        default: false,
    },
    canVerify: {
        type: Boolean,
        default: false,
    },
});

function getStatusBadgeClass(status) {
    switch (status) {
        case 'operational':
        case 'live_production':
        case 'fully_adopted':
            return 'badge-pass';
        case 'installed':
        case 'staff_trained':
        case 'mou_signed':
        case 'survey_conducted':
            return 'badge-warning';
        case 'decommissioned':
        case 'cancelled':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

function getVerificationBadge(status) {
    switch (status) {
        case 'verified': return 'badge-pass';
        case 'flagged': return 'badge-danger';
        case 'pending': return 'badge-warning';
        default: return 'badge-secondary';
    }
}

function formatInterventionTitle(type) {
    return type.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

function formatKeyLabel(key) {
    return key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
