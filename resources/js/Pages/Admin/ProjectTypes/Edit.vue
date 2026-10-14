<template>
    <AppShell>
        <div class="page-header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span :class="['badge', getKindBadgeClass(projectType.kind)]">
                        {{ projectType.kind.toUpperCase() }} ARCHETYPE
                    </span>
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%;" :style="{ background: projectType.color_code }"></span>
                </div>
                <h1 class="page-title">{{ projectType.name }}</h1>
                <p class="page-description">
                    Configure state transitions, terminal nodes, and dynamic custom field attributes.
                </p>
            </div>
            <div class="page-actions">
                <Link href="/admin/project-types" class="btn btn-secondary">
                    Back to Types
                </Link>
            </div>
        </div>

        <!-- Workflow Validation Status Banner -->
        <div v-if="workflowErrors.length > 0" class="alert alert-warning" style="margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong style="display: block; margin-bottom: 0.2rem;">Workflow Graph Warnings Detected:</strong>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.84rem;">
                    <li v-for="(err, idx) in workflowErrors" :key="idx">{{ err }}</li>
                </ul>
            </div>
        </div>
        <div v-else class="alert alert-pass" style="margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>State Machine Valid: All transition pathways and terminal states are fully linked.</span>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs-nav" style="margin-bottom: 1.5rem; width: fit-content;">
            <button class="tab-btn" :class="{ active: activeTab === 'workflow' }" @click="activeTab = 'workflow'">
                Status Workflow Sequencer ({{ projectType.status_defs?.length || 0 }})
            </button>
            <button class="tab-btn" :class="{ active: activeTab === 'fields' }" @click="activeTab = 'fields'">
                Custom Field Schema ({{ projectType.field_defs?.length || 0 }})
            </button>
            <button class="tab-btn" :class="{ active: activeTab === 'metadata' }" @click="activeTab = 'metadata'">
                Type Metadata & Styling
            </button>
        </div>

        <!-- TAB 1: Status Workflow Sequencer -->
        <div v-show="activeTab === 'workflow'">
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="card-header" style="padding-top: 0; padding-left: 0; padding-right: 0;">
                    <div>
                        <h2 class="card-title">Visual Status Pipeline Flow</h2>
                        <p style="font-size: 0.82rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Ordered progression of statuses. Transitions are strictly restricted to configured target states.
                        </p>
                    </div>
                </div>

                <!-- Interactive Visual Pipeline -->
                <div class="workflow-pipeline" style="margin-top: 1.25rem;">
                    <template v-for="(status, idx) in projectType.status_defs" :key="status.id">
                        <div
                            class="workflow-node"
                            :class="{ 'is-completed': status.is_completed, 'is-terminal': status.is_terminal }"
                        >
                            <div class="node-header">
                                <span class="badge" style="background: var(--c-surface-subtle); border-color: var(--c-border); font-size: 0.7rem;">
                                    #{{ status.sort_order }}
                                </span>
                                <div style="display: flex; gap: 0.25rem;">
                                    <span v-if="status.is_completed" class="badge badge-pass" title="Counts toward completion %">
                                        Completed
                                    </span>
                                    <span v-if="status.is_terminal" class="badge badge-danger" title="Terminal state (no transitions allowed out)">
                                        Terminal
                                    </span>
                                </div>
                            </div>

                            <div class="node-title">{{ status.label }}</div>
                            <div class="node-key">{{ status.key }}</div>

                            <!-- Allowed Next Status Keys -->
                            <div style="margin-top: 0.5rem; pt: 0.35rem; border-top: 1px dashed var(--c-border-subtle);">
                                <div style="font-size: 0.68rem; text-transform: uppercase; color: var(--c-ink-subtle); margin-bottom: 0.2rem;">
                                    Permitted Next:
                                </div>
                                <div v-if="status.is_terminal" style="font-size: 0.72rem; color: var(--scale-danger); font-style: italic;">
                                    None (Terminal)
                                </div>
                                <div v-else-if="status.next_keys && status.next_keys.length > 0" style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    <span v-for="nk in status.next_keys" :key="nk" class="scope-chip" style="font-size: 0.68rem;">
                                        {{ nk }}
                                    </span>
                                </div>
                                <div v-else style="font-size: 0.72rem; color: var(--c-ink-subtle); font-style: italic;">
                                    Sequential next (#{{ status.sort_order + 1 }})
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                                <button
                                    class="btn btn-ghost btn-sm"
                                    style="color: var(--scale-danger); font-size: 0.72rem; padding: 0.15rem 0.4rem;"
                                    @click="deleteStatus(status)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Transition Arrow between steps -->
                        <div v-if="idx < projectType.status_defs.length - 1" class="workflow-arrow">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Add/Edit Status Node Form -->
            <div class="card" style="padding: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 1rem;">Add New Status to Pipeline</h3>
                <form @submit.prevent="submitStatus">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label required">Status Key (Machine ID)</label>
                            <input
                                type="text"
                                v-model="statusForm.key"
                                placeholder="e.g. testing_verified"
                                class="form-control font-mono"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Human Label</label>
                            <input
                                type="text"
                                v-model="statusForm.label"
                                placeholder="e.g. Testing & Verification"
                                class="form-control"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Sort Order</label>
                            <input
                                type="number"
                                v-model.number="statusForm.sort_order"
                                class="form-control font-mono"
                                required
                            />
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; justify-content: center; gap: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; cursor: pointer;">
                                <input type="checkbox" v-model="statusForm.is_completed" />
                                <span>Marks Site as Completed (Counts toward 100%)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; cursor: pointer;">
                                <input type="checkbox" v-model="statusForm.is_terminal" />
                                <span>Is Terminal Node (No further transitions)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Target Allowed Transitions -->
                    <div class="form-group" style="margin-top: 1rem;">
                        <label class="form-label">Permitted Next Transitions (Target Statuses)</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.65rem; margin-top: 0.35rem;">
                            <label
                                v-for="s in projectType.status_defs"
                                :key="s.id"
                                style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; padding: 0.35rem 0.65rem; background: var(--c-surface-subtle); border-radius: var(--radius-sm); border: 1px solid var(--c-border); cursor: pointer;"
                            >
                                <input
                                    type="checkbox"
                                    :value="s.key"
                                    v-model="statusForm.next_keys"
                                />
                                <span class="font-mono">{{ s.key }}</span>
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem;">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="statusForm.processing">
                            Save Status Definition
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: Custom Field Schema -->
        <div v-show="activeTab === 'fields'">
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="card-header" style="padding-top: 0; padding-left: 0; padding-right: 0;">
                    <div>
                        <h2 class="card-title">Dynamic Field Definitions</h2>
                        <p style="font-size: 0.82rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Configured custom attributes dynamically rendered in field reporting forms and site dossiers.
                        </p>
                    </div>
                </div>

                <div v-if="projectType.field_defs?.length > 0" style="margin-top: 1rem; overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Order</th>
                                <th>Field Label & Key</th>
                                <th>Data Type</th>
                                <th>Scope</th>
                                <th>Required</th>
                                <th>Options / Configuration</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="f in projectType.field_defs" :key="f.id">
                                <td class="font-mono">#{{ f.sort_order }}</td>
                                <td>
                                    <div style="font-weight: 600; color: var(--c-ink);">{{ f.label }}</div>
                                    <div style="font-family: var(--font-mono); font-size: 0.74rem; color: var(--c-ink-subtle);">{{ f.key }}</div>
                                </td>
                                <td>
                                    <span class="badge" style="background: var(--c-surface-subtle); border-color: var(--c-border); text-transform: uppercase;">
                                        {{ f.type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="scope-chip">{{ f.applies_to }}</span>
                                </td>
                                <td>
                                    <span v-if="f.required" class="badge badge-danger">Required</span>
                                    <span v-else class="badge badge-secondary">Optional</span>
                                </td>
                                <td>
                                    <span v-if="f.options && f.options.length > 0" style="font-size: 0.78rem; color: var(--c-ink-muted);">
                                        {{ f.options.join(', ') }}
                                    </span>
                                    <span v-else style="color: var(--c-ink-subtle); font-size: 0.75rem;">—</span>
                                </td>
                                <td>
                                    <button class="btn btn-ghost btn-sm" style="color: var(--scale-danger);" @click="deleteField(f)">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else style="padding: 2rem; text-align: center; color: var(--c-ink-muted);">
                    No custom fields configured for this project archetype.
                </div>
            </div>

            <!-- Add Field Form -->
            <div class="card" style="padding: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 1rem;">Add Custom Attribute</h3>
                <form @submit.prevent="submitField">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label required">Attribute Key</label>
                            <input
                                type="text"
                                v-model="fieldForm.key"
                                placeholder="e.g. equipment_serial"
                                class="form-control font-mono"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Display Label</label>
                            <input
                                type="text"
                                v-model="fieldForm.label"
                                placeholder="e.g. Primary Router Serial Number"
                                class="form-control"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Data Type</label>
                            <select v-model="fieldForm.type" class="form-control" required>
                                <option value="text">Text (Single-line)</option>
                                <option value="number">Number (Decimal)</option>
                                <option value="integer">Integer (Whole number)</option>
                                <option value="select">Dropdown Select</option>
                                <option value="multiselect">Multi-select Tags</option>
                                <option value="date">Date</option>
                                <option value="boolean">Boolean Switch</option>
                                <option value="sex_disaggregated">Sex-Disaggregated (M/F GAD)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Applies To</label>
                            <select v-model="fieldForm.applies_to" class="form-control" required>
                                <option value="site">Site / Location Attribute</option>
                                <option value="report">Intervention Report</option>
                                <option value="activity">Training / Cohort Activity</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Sort Order</label>
                            <input
                                type="number"
                                v-model.number="fieldForm.sort_order"
                                class="form-control font-mono"
                                required
                            />
                        </div>
                    </div>

                    <!-- Options input for select/multiselect -->
                    <div v-if="['select', 'multiselect'].includes(fieldForm.type)" class="form-group" style="margin-top: 1rem;">
                        <label class="form-label">Dropdown Options (Comma-separated)</label>
                        <input
                            type="text"
                            v-model="fieldOptionsString"
                            placeholder="Option 1, Option 2, Option 3"
                            class="form-control"
                        />
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; cursor: pointer;">
                            <input type="checkbox" v-model="fieldForm.required" />
                            <span>Mandatory field in submission forms</span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem;">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="fieldForm.processing">
                            Save Field Definition
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 3: Type Metadata & Styling -->
        <div v-show="activeTab === 'metadata'">
            <div class="card" style="padding: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 1rem;">Archetype Identity & Marker Styling</h3>
                <form @submit.prevent="submitMetadata">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label required">Archetype Name</label>
                            <input type="text" v-model="metaForm.name" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Marker Shape on Leaflet Map</label>
                            <select v-model="metaForm.marker_shape" class="form-control" required>
                                <option value="circle">Circle (Kind A Default)</option>
                                <option value="square">Square (Kind B Default)</option>
                                <option value="diamond">Diamond (Kind C Default)</option>
                                <option value="triangle">Triangle (Kind D Default)</option>
                                <option value="pin">Map Pin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Color Hex Code</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="color" v-model="metaForm.color_code" style="width: 42px; height: 38px; border: 1px solid var(--c-border); border-radius: var(--radius-sm); cursor: pointer;" />
                                <input type="text" v-model="metaForm.color_code" class="form-control font-mono" style="width: 140px;" required />
                            </div>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Description</label>
                            <textarea v-model="metaForm.description" rows="3" class="form-control"></textarea>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary" :disabled="metaForm.processing">
                            Update Archetype Metadata
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    projectType: {
        type: Object,
        required: true,
    },
    workflowErrors: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('workflow');

// Status Form
const statusForm = useForm({
    key: '',
    label: '',
    sort_order: (props.projectType.status_defs?.length || 0) + 1,
    is_completed: false,
    is_terminal: false,
    next_keys: [],
});

function submitStatus() {
    statusForm.post(`/admin/project-types/${props.projectType.id}/statuses`, {
        preserveScroll: true,
        onSuccess: () => {
            statusForm.reset('key', 'label', 'next_keys');
            statusForm.sort_order = (props.projectType.status_defs?.length || 0) + 1;
        },
    });
}

function deleteStatus(status) {
    if (confirm(`Are you sure you want to remove status '${status.label}'?`)) {
        router.delete(`/admin/project-types/${props.projectType.id}/statuses/${status.id}`, {
            preserveScroll: true,
        });
    }
}

// Field Form
const fieldOptionsString = ref('');
const fieldForm = useForm({
    key: '',
    label: '',
    type: 'text',
    applies_to: 'report',
    required: false,
    sort_order: (props.projectType.field_defs?.length || 0) + 1,
    options: [],
});

function submitField() {
    if (['select', 'multiselect'].includes(fieldForm.type) && fieldOptionsString.value) {
        fieldForm.options = fieldOptionsString.value
            .split(',')
            .map(s => s.trim())
            .filter(Boolean);
    } else {
        fieldForm.options = null;
    }

    fieldForm.post(`/admin/project-types/${props.projectType.id}/fields`, {
        preserveScroll: true,
        onSuccess: () => {
            fieldForm.reset('key', 'label');
            fieldOptionsString.value = '';
            fieldForm.sort_order = (props.projectType.field_defs?.length || 0) + 1;
        },
    });
}

function deleteField(field) {
    if (confirm(`Remove custom attribute '${field.label}'?`)) {
        router.delete(`/admin/project-types/${props.projectType.id}/fields/${field.id}`, {
            preserveScroll: true,
        });
    }
}

// Metadata Form
const metaForm = useForm({
    name: props.projectType.name,
    marker_shape: props.projectType.marker_shape,
    color_code: props.projectType.color_code,
    description: props.projectType.description || '',
});

function submitMetadata() {
    metaForm.put(`/admin/project-types/${props.projectType.id}`, {
        preserveScroll: true,
    });
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
</script>
