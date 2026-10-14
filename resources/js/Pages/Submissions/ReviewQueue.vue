<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">QA Verification Queue</h1>
                <p class="page-description">
                    Review incoming field engineering submissions, verify telemetry measurements, and inspect anti-fraud alerts.
                </p>
            </div>
            <div class="page-actions">
                <Link href="/sites" class="btn btn-secondary">
                    Sites Registry
                </Link>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="metric-grid" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <span class="metric-label">Pending Verification</span>
                <span class="metric-value" style="color: var(--scale-warning);">{{ stats.pending_count }}</span>
                <span class="metric-sub">Awaiting reviewer sign-off</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Anti-Fraud Flagged</span>
                <span class="metric-value" style="color: var(--scale-danger);">{{ stats.flagged_count }}</span>
                <span class="metric-sub">GPS or hash anomalies</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Verified & Commissioned</span>
                <span class="metric-value" style="color: var(--scale-pass);">{{ stats.verified_count }}</span>
                <span class="metric-sub">Approved milestones</span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Rejected Submissions</span>
                <span class="metric-value">{{ stats.rejected_count }}</span>
                <span class="metric-sub">Sent back for rectification</span>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="tabs-nav" style="margin-bottom: 1.5rem; width: fit-content;">
            <button class="tab-btn" :class="{ active: currentStatus === 'pending' || !currentStatus }" @click="filterStatus('pending')">
                Pending Queue ({{ stats.pending_count }})
            </button>
            <button class="tab-btn" :class="{ active: currentStatus === 'flagged' }" @click="filterStatus('flagged')">
                Flagged Alerts ({{ stats.flagged_count }})
            </button>
            <button class="tab-btn" :class="{ active: currentStatus === 'verified' }" @click="filterStatus('verified')">
                Verified ({{ stats.verified_count }})
            </button>
            <button class="tab-btn" :class="{ active: currentStatus === 'rejected' }" @click="filterStatus('rejected')">
                Rejected ({{ stats.rejected_count }})
            </button>
        </div>

        <!-- Reports List / Cards -->
        <div v-if="reports.data.length > 0" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div v-for="rep in reports.data" :key="rep.id" class="card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span class="project-code-badge">{{ rep.intervention?.project_site?.site_code }}</span>
                            <span style="font-weight: 700; font-size: 1.05rem; color: var(--c-ink);">
                                {{ rep.intervention?.project_site?.location?.name || 'Site' }}
                            </span>
                            <span :class="['badge', getVerificationBadge(rep.verification_status)]">
                                {{ rep.verification_status.toUpperCase() }}
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--c-ink-muted);">
                            <span>Event: <strong>{{ rep.intervention?.intervention_type?.replace('_', ' ') }}</strong></span>
                            <span style="margin-left: 0.75rem;">Transition: <strong>{{ rep.intervention?.previous_status }} → {{ rep.intervention?.new_status }}</strong></span>
                            <span style="margin-left: 0.75rem;">Project: <strong>{{ rep.intervention?.project_site?.project?.name }}</strong></span>
                        </div>
                    </div>

                    <div style="text-align: right; font-size: 0.76rem; color: var(--c-ink-muted);">
                        <div>Reported by <strong>{{ rep.intervention?.author?.name }}</strong></div>
                        <div class="font-mono">{{ formatDate(rep.server_timestamp) }}</div>
                    </div>
                </div>

                <!-- Anti-Fraud Warning Callout -->
                <div v-if="rep.verification_flags && rep.verification_flags.length > 0" class="alert alert-warning" style="margin-top: 1rem; padding: 0.65rem 0.85rem; font-size: 0.8rem;">
                    <strong>Anti-Fraud Inspection Flagged:</strong>
                    <div style="display: flex; gap: 0.35rem; margin-top: 0.25rem;">
                        <span v-for="fl in rep.verification_flags" :key="fl" class="flag-chip">
                            ⚠ {{ fl }}
                        </span>
                    </div>
                </div>

                <!-- Report Content -->
                <p v-if="rep.notes" style="font-size: 0.88rem; color: var(--c-ink); line-height: 1.45; margin: 0.85rem 0;">
                    {{ rep.notes }}
                </p>

                <!-- Attached Photos -->
                <div v-if="rep.attachments && rep.attachments.length > 0" class="evidence-gallery" style="margin-bottom: 0.85rem;">
                    <a
                        v-for="att in rep.attachments"
                        :key="att.id"
                        :href="att.file_path"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img :src="att.file_path" :alt="att.file_name" class="evidence-thumb" />
                    </a>
                </div>

                <!-- Review Actions Form (for pending/flagged reports) -->
                <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid var(--c-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; max-width: 500px;">
                        <input
                            type="text"
                            v-model="reviewNotesMap[rep.id]"
                            placeholder="Optional verification note or reason for rejection..."
                            class="form-control"
                            style="font-size: 0.8rem; padding: 0.35rem 0.65rem;"
                        />
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <Link :href="'/sites/' + rep.intervention?.project_site_id" class="btn btn-secondary btn-sm">
                            View Dossier
                        </Link>
                        <button
                            class="btn btn-sm"
                            style="background: var(--scale-pass); color: white;"
                            @click="submitVerification(rep.id, 'verified')"
                        >
                            ✓ Approve & Verify
                        </button>
                        <button
                            v-if="rep.verification_status !== 'flagged'"
                            class="btn btn-sm btn-secondary"
                            style="color: var(--scale-warning);"
                            @click="submitVerification(rep.id, 'flagged')"
                        >
                            ⚠ Flag
                        </button>
                        <button
                            class="btn btn-danger btn-sm"
                            @click="submitVerification(rep.id, 'rejected')"
                        >
                            ✕ Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card" style="padding: 3rem 1rem; text-align: center; color: var(--c-ink-muted);">
            No field reports currently in this verification queue.
        </div>
    </AppShell>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    reports: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const currentStatus = ref(props.filters.status || 'pending');
const reviewNotesMap = reactive({});

function filterStatus(status) {
    currentStatus.value = status;
    router.get('/reviews', { status: status }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function submitVerification(reportId, targetStatus) {
    const notes = reviewNotesMap[reportId] || '';
    router.post(`/reviews/${reportId}/verify`, {
        status: targetStatus,
        review_notes: notes,
    }, {
        preserveScroll: true,
    });
}

function getVerificationBadge(status) {
    switch (status) {
        case 'verified': return 'badge-pass';
        case 'flagged': return 'badge-danger';
        case 'pending': return 'badge-warning';
        case 'rejected': return 'badge-danger';
        default: return 'badge-secondary';
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
