<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Project Types & Workflow Architecture</h1>
                <p class="page-description">
                    Configure tracking archetypes, state-machine transition rules, and dynamic custom field schemas.
                </p>
            </div>
            <div class="page-actions">
                <Link href="/projects" class="btn btn-secondary">
                    Back to Portfolio
                </Link>
            </div>
        </div>

        <div class="portfolio-grid">
            <div v-for="pt in projectTypes" :key="pt.id" class="card card-interactive" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem;">
                        <span :class="['badge', getKindBadgeClass(pt.kind)]">
                            {{ pt.kind.toUpperCase() }} ARCHETYPE
                        </span>
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span style="font-size: 0.76rem; color: var(--c-ink-subtle);">Marker: {{ pt.marker_shape }}</span>
                            <span style="width: 12px; height: 12px; border-radius: 50%; display: inline-block;" :style="{ background: pt.color_code }"></span>
                        </div>
                    </div>

                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--c-ink); margin-bottom: 0.35rem;">
                        {{ pt.name }}
                    </h2>
                    <p style="font-size: 0.84rem; color: var(--c-ink-muted); line-height: 1.4; margin-bottom: 1.25rem;">
                        {{ pt.description || 'No description provided.' }}
                    </p>

                    <!-- Architecture Stats Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; background: var(--c-surface-subtle); padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--c-border);">
                        <div>
                            <div style="font-size: 0.72rem; color: var(--c-ink-muted); text-transform: uppercase;">Active Projects</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--c-ink);">{{ pt.projects_count }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; color: var(--c-ink-muted); text-transform: uppercase;">Statuses In Pipeline</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--c-primary);">{{ pt.status_defs_count }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; color: var(--c-ink-muted); text-transform: uppercase;">Custom Fields</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--scale-pass);">{{ pt.field_defs_count }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; color: var(--c-ink-muted); text-transform: uppercase;">Intervention Types</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--scale-warning);">{{ pt.intervention_types_count }}</div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; pt: 1rem; border-top: 1px solid var(--c-border-subtle);">
                    <Link :href="'/admin/project-types/' + pt.id + '/edit'" class="btn btn-outline" style="width: 100%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        <span>Configure Workflow & Schema</span>
                    </Link>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    projectTypes: {
        type: Array,
        default: () => [],
    },
});

function getKindBadgeClass(kind) {
    switch (kind) {
        case 'site': return 'badge-kind-a';
        case 'institution': return 'badge-kind-b';
        case 'activity': return 'badge-kind-c';
        case 'milestone': return 'badge-kind-d';
        default: return 'badge-secondary';
    }
}
</script>
