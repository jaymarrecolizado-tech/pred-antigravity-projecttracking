<template>
    <AppShell>
        <div class="page-header">
            <div>
                <h1 class="page-title">Create New Project</h1>
                <p class="page-description">
                    Define project taxonomy, configure administrative geographic scope, and assign indicator targets.
                </p>
            </div>
            <div class="page-actions">
                <Link href="/projects" class="btn btn-secondary">
                    Cancel
                </Link>
            </div>
        </div>

        <!-- Wizard Stepper Indicator -->
        <div class="wizard-header">
            <div class="wizard-steps">
                <div class="wizard-step" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
                    <div class="step-num">{{ currentStep > 1 ? '✓' : '1' }}</div>
                    <div class="step-label">
                        <span class="step-title">Classification & Metadata</span>
                        <span class="step-desc">Program, Type & Budget</span>
                    </div>
                </div>
                <div class="wizard-step" :class="{ active: currentStep === 2, completed: currentStep > 2 }">
                    <div class="step-num">{{ currentStep > 2 ? '✓' : '2' }}</div>
                    <div class="step-label">
                        <span class="step-title">Geographic Scope</span>
                        <span class="step-desc">Target Provinces & LGUs</span>
                    </div>
                </div>
                <div class="wizard-step" :class="{ active: currentStep === 3 }">
                    <div class="step-num">3</div>
                    <div class="step-label">
                        <span class="step-title">Performance Targets</span>
                        <span class="step-desc">Quantified KPIs & Timeline</span>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm">
            <!-- STEP 1: Metadata & Classification -->
            <div v-show="currentStep === 1" class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="card-header" style="padding-top: 0; padding-left: 0; padding-right: 0;">
                    <h2 class="card-title">Step 1: Project Metadata & Archetype</h2>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label required">Umbrella Program</label>
                        <select v-model="form.program_id" class="form-control" required @change="onProgramChange">
                            <option value="">Select DICT Program (29 available)</option>
                            <option v-for="prog in programs" :key="prog.id" :value="prog.id">
                                {{ prog.name }}
                            </option>
                        </select>
                        <span v-if="errors.program_id" class="form-error">{{ errors.program_id }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Project Type (Tracking Archetype)</label>
                        <select v-model="form.project_type_id" class="form-control" required>
                            <option value="">Select Archetype</option>
                            <option v-for="pt in projectTypes" :key="pt.id" :value="pt.id">
                                {{ pt.name }} ({{ pt.kind.toUpperCase() }})
                            </option>
                        </select>
                        <span v-if="errors.project_type_id" class="form-error">{{ errors.project_type_id }}</span>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label required">Project Name</label>
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="e.g. Free Wi-Fi Rollout 2026 Phase 2 - Isabela & Quirino"
                            class="form-control"
                            required
                        />
                        <span v-if="errors.name" class="form-error">{{ errors.name }}</span>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label class="form-label required" style="margin-bottom: 0;">Project Code</label>
                            <button type="button" class="btn btn-ghost btn-sm" @click="generateCode" style="font-size: 0.75rem; padding: 0.15rem 0.45rem;">
                                Auto-Generate
                            </button>
                        </div>
                        <input
                            type="text"
                            v-model="form.code"
                            placeholder="e.g. FW4A-2026-R2-P2"
                            class="form-control font-mono"
                            required
                        />
                        <span v-if="errors.code" class="form-error">{{ errors.code }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Lifecycle Status</label>
                        <select v-model="form.lifecycle" class="form-control" required>
                            <option value="draft">Draft (Planning & Scoping)</option>
                            <option value="active">Active (Under Implementation)</option>
                            <option value="closed">Closed (Completed)</option>
                            <option value="archived">Archived</option>
                        </select>
                        <span v-if="errors.lifecycle" class="form-error">{{ errors.lifecycle }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total Allocated Budget (PHP)</label>
                        <input
                            type="number"
                            step="0.01"
                            v-model="form.budget"
                            placeholder="e.g. 15000000.00"
                            class="form-control font-mono"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Funding Source</label>
                        <input
                            type="text"
                            v-model="form.funding_source"
                            placeholder="e.g. GAA 2026 Free Wi-Fi Fund, LGU counterpart"
                            class="form-control"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Start Date</label>
                        <input type="date" v-model="form.start_date" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Completion Date</label>
                        <input type="date" v-model="form.end_date" class="form-control" />
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-primary" @click="goToStep(2)">
                        <span>Continue to Geographic Scope</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Geographic Scope Allocation -->
            <div v-show="currentStep === 2" class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="card-header" style="padding-top: 0; padding-left: 0; padding-right: 0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 class="card-title">Step 2: Geographic Scope Allocation</h2>
                        <p style="font-size: 0.82rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Select which provinces or specific municipalities in Region 2 this project is budgeted for.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-secondary btn-sm" @click="selectAllProvinces">
                            Select Entire Region 2
                        </button>
                        <button type="button" class="btn btn-ghost btn-sm" @click="clearScopeSelection">
                            Clear
                        </button>
                    </div>
                </div>

                <div style="margin: 1rem 0; padding: 0.75rem 1rem; background: var(--c-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--c-border); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.88rem; font-weight: 500; color: var(--c-ink);">
                        Targeted Administrative Areas:
                    </span>
                    <span class="badge badge-pass" style="font-size: 0.85rem;">
                        {{ form.scopes.length }} Areas Selected
                    </span>
                </div>

                <!-- Provinces Accordion List -->
                <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 480px; overflow-y: auto; padding-right: 0.5rem;">
                    <div v-for="prov in provinces" :key="prov.code" style="border: 1px solid var(--c-border); border-radius: var(--radius-md); padding: 0.85rem; background: var(--c-surface);">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <label style="display: flex; align-items: center; gap: 0.65rem; font-weight: 600; font-size: 0.95rem; cursor: pointer;">
                                <input
                                    type="checkbox"
                                    :value="prov.code"
                                    :checked="isProvinceFullySelected(prov.code)"
                                    @change="toggleProvince(prov.code)"
                                />
                                <span>{{ prov.name }}</span>
                            </label>

                            <button type="button" class="btn btn-ghost btn-sm" @click="toggleExpandProvince(prov.code)" style="font-size: 0.76rem;">
                                {{ expandedProvinces.includes(prov.code) ? 'Hide LGUs ▲' : 'Show LGUs (' + getMunicipalitiesForProvince(prov.code).length + ') ▼' }}
                            </button>
                        </div>

                        <!-- Municipalities Grid -->
                        <div v-if="expandedProvinces.includes(prov.code)" style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--c-border); display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem;">
                            <label v-for="mun in getMunicipalitiesForProvince(prov.code)" :key="mun.code" style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; cursor: pointer; color: var(--c-ink-muted);">
                                <input
                                    type="checkbox"
                                    :value="mun.code"
                                    v-model="form.scopes"
                                />
                                <span>{{ mun.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" @click="goToStep(1)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Back to Metadata</span>
                    </button>
                    <button type="button" class="btn btn-primary" @click="goToStep(3)">
                        <span>Continue to Targets</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Indicator Targets & Submission -->
            <div v-show="currentStep === 3" class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="card-header" style="padding-top: 0; padding-left: 0; padding-right: 0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 class="card-title">Step 3: Performance Targets & Milestones</h2>
                        <p style="font-size: 0.82rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Define quantifiable quarterly and annual targets for regional accountability.
                        </p>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" @click="addTargetRow">
                        + Add Target Indicator
                    </button>
                </div>

                <div v-if="form.targets.length > 0" style="margin-top: 1rem; overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Indicator Key</th>
                                <th>Period</th>
                                <th>Geographic Scope</th>
                                <th>Target Value</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tgt, index) in form.targets" :key="index">
                                <td>
                                    <input
                                        type="text"
                                        v-model="tgt.indicator_key"
                                        placeholder="e.g. operational_hotspots"
                                        class="form-control font-mono"
                                        required
                                        style="font-size: 0.82rem;"
                                    />
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        v-model="tgt.period"
                                        placeholder="e.g. 2026 or 2026-Q1"
                                        class="form-control font-mono"
                                        required
                                        style="width: 120px; font-size: 0.82rem;"
                                    />
                                </td>
                                <td>
                                    <select v-model="tgt.psgc_code" class="form-control" style="font-size: 0.82rem;">
                                        <option :value="null">Regional Scope (All R2)</option>
                                        <option v-for="prov in provinces" :key="prov.code" :value="prov.code">
                                            {{ prov.name }} (Province)
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="1"
                                        v-model="tgt.target_value"
                                        class="form-control font-mono"
                                        required
                                        style="width: 120px; font-size: 0.82rem;"
                                    />
                                </td>
                                <td>
                                    <button type="button" class="btn btn-ghost btn-sm" @click="removeTargetRow(index)" style="color: var(--scale-danger);">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else style="padding: 2rem; text-align: center; color: var(--c-ink-muted); border: 1px dashed var(--c-border); border-radius: var(--radius-md); margin-top: 1rem;">
                    No indicator targets added yet. Click <strong>"+ Add Target Indicator"</strong> above to assign physical target numbers.
                </div>

                <!-- Review Summary Box -->
                <div style="margin-top: 2rem; padding: 1.25rem; background: var(--c-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--c-border);">
                    <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--c-ink); margin-bottom: 0.5rem;">
                        Project Summary
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.82rem;">
                        <div>
                            <span style="color: var(--c-ink-muted);">Code:</span>
                            <span style="font-weight: 600; margin-left: 0.35rem;">{{ form.code || 'None' }}</span>
                        </div>
                        <div>
                            <span style="color: var(--c-ink-muted);">Name:</span>
                            <span style="font-weight: 600; margin-left: 0.35rem;">{{ form.name || 'None' }}</span>
                        </div>
                        <div>
                            <span style="color: var(--c-ink-muted);">Lifecycle:</span>
                            <span style="font-weight: 600; margin-left: 0.35rem;">{{ form.lifecycle }}</span>
                        </div>
                        <div>
                            <span style="color: var(--c-ink-muted);">Target Areas:</span>
                            <span style="font-weight: 600; margin-left: 0.35rem;">{{ form.scopes.length }} selected</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" @click="goToStep(2)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Back to Scope</span>
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        <span>Save & Instantiate Project</span>
                    </button>
                </div>
            </div>
        </form>
    </AppShell>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    programs: {
        type: Array,
        default: () => [],
    },
    projectTypes: {
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
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const currentStep = ref(1);
const expandedProvinces = ref([]);

const form = useForm({
    program_id: '',
    project_type_id: '',
    name: '',
    code: '',
    lifecycle: 'active',
    budget: '',
    funding_source: 'GAA 2026',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    color: '#2563eb',
    scopes: [],
    targets: [
        { indicator_key: 'target_units', period: '2026', psgc_code: null, target_value: 100 }
    ],
});

function goToStep(step) {
    if (step === 2 && (!form.name || !form.code || !form.project_type_id)) {
        alert('Please complete the project name, code, and project type first.');
        return;
    }
    currentStep.value = step;
}

function onProgramChange() {
    const selected = props.programs.find(p => p.id === form.program_id);
    if (selected && !form.name) {
        form.name = `${selected.name} 2026`;
    }
    generateCode();
}

function generateCode() {
    const selectedProg = props.programs.find(p => p.id === form.program_id);
    let prefix = 'PRJ';
    if (selectedProg) {
        prefix = selectedProg.name.replace(/[^A-Za-z0-9]/g, '').slice(0, 4).toUpperCase();
    }
    const rand = Math.floor(100 + Math.random() * 900);
    form.code = `${prefix}-2026-R2-${rand}`;
}

function toggleExpandProvince(provCode) {
    const idx = expandedProvinces.value.indexOf(provCode);
    if (idx >= 0) {
        expandedProvinces.value.splice(idx, 1);
    } else {
        expandedProvinces.value.push(provCode);
    }
}

function getMunicipalitiesForProvince(provCode) {
    return props.municipalities.filter(m => m.parent_code === provCode);
}

function isProvinceFullySelected(provCode) {
    return form.scopes.includes(provCode);
}

function toggleProvince(provCode) {
    const muns = getMunicipalitiesForProvince(provCode).map(m => m.code);
    const hasProvince = form.scopes.includes(provCode);

    if (hasProvince) {
        // Remove province and its municipalities
        form.scopes = form.scopes.filter(code => code !== provCode && !muns.includes(code));
    } else {
        // Add province and its municipalities
        form.scopes = Array.from(new Set([...form.scopes, provCode, ...muns]));
    }
}

function selectAllProvinces() {
    const allCodes = [
        ...props.provinces.map(p => p.code),
        ...props.municipalities.map(m => m.code),
    ];
    form.scopes = Array.from(new Set(allCodes));
}

function clearScopeSelection() {
    form.scopes = [];
}

function addTargetRow() {
    form.targets.push({
        indicator_key: 'target_units',
        period: '2026',
        psgc_code: null,
        target_value: 50,
    });
}

function removeTargetRow(index) {
    form.targets.splice(index, 1);
}

function submitForm() {
    form.post('/projects', {
        onSuccess: () => {
            // Flash message handled by inertia
        },
    });
}
</script>
