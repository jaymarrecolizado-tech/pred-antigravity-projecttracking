<template>
    <AppShell>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-group">
                <h1>Immutable Audit Trail & Security Ledger</h1>
                <div class="page-subtitle">
                    Tamper-evident append-only ledger tracking all administrative actions, user grants, and report dispatches
                </div>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <span class="chip-status status-online">
                    <span class="status-dot"></span>
                    <span style="font-family: var(--font-mono); font-weight: 600;">APPEND-ONLY INTEGRITY ACTIVE</span>
                </span>
            </div>
        </div>

        <!-- Audit Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Chronological Event Logs</div>
                    <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                        Total events recorded: {{ logs.total }}
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Action</th>
                            <th>Actor / User</th>
                            <th>IP Address</th>
                            <th>Entity / Target</th>
                            <th>State Diff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--c-ink-muted);">
                                No audit logs recorded in system ledger yet.
                            </td>
                        </tr>
                        <tr v-for="log in logs.data" :key="log.id">
                            <td style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--c-ink); white-space: nowrap;">
                                {{ log.created_at }}
                            </td>
                            <td>
                                <span class="badge" :class="getActionBadgeClass(log.action)">
                                    {{ log.action }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--c-ink);">{{ log.user_name }}</div>
                                <div style="font-size: 0.72rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                                    {{ log.user_email }}
                                </div>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--c-ink-muted);">
                                {{ log.ip_address }}
                            </td>
                            <td>
                                <span v-if="log.auditable_type" style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--c-ink);">
                                    {{ log.auditable_type }} #{{ log.auditable_id }}
                                </span>
                                <span v-else style="color: var(--c-ink-muted); font-size: 0.75rem;">System</span>
                            </td>
                            <td>
                                <button v-if="log.before_state || log.after_state"
                                        type="button"
                                        @click="viewDiff(log)"
                                        class="btn btn-ghost btn-sm">
                                    Inspect State
                                </button>
                                <span v-else style="color: var(--c-ink-muted); font-size: 0.75rem;">None</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div v-if="logs.links && logs.links.length > 3" style="display: flex; justify-content: center; gap: 0.4rem; padding: 1rem; border-top: 1px solid var(--c-border);">
                <Link v-for="(link, i) in logs.links" :key="i"
                      :href="link.url || '#'"
                      v-html="link.label"
                      class="btn btn-sm"
                      :class="link.active ? 'btn-primary' : 'btn-ghost'"
                      :style="{ pointerEvents: link.url ? 'auto' : 'none', opacity: link.url ? 1 : 0.5 }">
                </Link>
            </div>
        </div>

        <!-- State Diff Modal -->
        <div v-if="selectedLog" class="modal-overlay" @click.self="selectedLog = null">
            <div class="modal-card" style="max-width: 680px;">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--c-ink); margin: 0;">Audit Event #{{ selectedLog.id }} State Diff</h3>
                        <div style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Action: {{ selectedLog.action }} • By: {{ selectedLog.user_name }}
                        </div>
                    </div>
                    <button type="button" @click="selectedLog = null" class="btn btn-ghost btn-sm">✕</button>
                </div>
                <div style="padding: 1.25rem; max-height: 70vh; overflow-y: auto;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.78rem; color: var(--scale-danger); margin-bottom: 0.4rem; text-transform: uppercase;">
                                Before State
                            </div>
                            <pre style="background: var(--c-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm); font-size: 0.75rem; font-family: var(--font-mono); overflow-x: auto; border: 1px solid var(--c-border); white-space: pre-wrap;">{{ selectedLog.before_state ? JSON.stringify(selectedLog.before_state, null, 2) : '(Initial Creation - Null)' }}</pre>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.78rem; color: var(--scale-success); margin-bottom: 0.4rem; text-transform: uppercase;">
                                After State
                            </div>
                            <pre style="background: var(--c-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm); font-size: 0.75rem; font-family: var(--font-mono); overflow-x: auto; border: 1px solid var(--c-border); white-space: pre-wrap;">{{ selectedLog.after_state ? JSON.stringify(selectedLog.after_state, null, 2) : '(Deletions / Null)' }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
});

const selectedLog = ref(null);

function viewDiff(log) {
    selectedLog.value = log;
}

function getActionBadgeClass(action) {
    if (action.includes('delete')) return 'badge-danger';
    if (action.includes('create') || action.includes('export')) return 'badge-success';
    if (action.includes('verify')) return 'badge-primary';
    return 'badge-neutral';
}
</script>

<style scoped>
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
