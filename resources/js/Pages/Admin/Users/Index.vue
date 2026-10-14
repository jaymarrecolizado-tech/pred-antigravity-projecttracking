<template>
    <AppShell>
        <div class="page-header">
            <div class="page-title-group">
                <h1>User Management & Scoped Grants</h1>
                <div class="page-subtitle">
                    Configure personnel access, regional roles, and project/geographic permissions
                </div>
            </div>
            <div>
                <button class="btn btn-primary" @click="showCreateModal = true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Add Regional User</span>
                </button>
            </div>
        </div>

        <!-- Success/Alert Notice -->
        <div v-if="$page.props.flash?.success" class="card" style="margin-bottom: 1.25rem; background: var(--scale-success-bg); border-color: var(--scale-success-border); padding: 0.85rem 1.25rem;">
            <div style="font-size: 0.85rem; font-weight: 500; color: var(--scale-success);">
                {{ $page.props.flash.success }}
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Registered Personnel ({{ users.length }})</div>
            </div>
            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>User Name & Email</th>
                            <th>Agency / Unit</th>
                            <th>2FA Status</th>
                            <th>Scoped Role Grants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td>
                                <div style="font-weight: 600; color: var(--c-ink);">{{ user.name }}</div>
                                <div style="font-size: 0.78rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                                    {{ user.email }} • {{ user.phone || 'No phone' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--c-ink-secondary);">
                                    {{ user.agency_unit || 'DICT R2 Central' }}
                                </span>
                            </td>
                            <td>
                                <span v-if="user.two_factor_enabled" class="chip-status status-online">
                                    <span class="status-dot"></span>
                                    <span>2FA Active</span>
                                </span>
                                <span v-else class="chip-status status-planned">
                                    <span class="status-dot"></span>
                                    <span>Not Configured</span>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                    <span v-for="g in user.role_grants" :key="g.id" class="badge badge-kind-a" style="text-transform: none; font-size: 0.75rem;">
                                        <strong>{{ formatRole(g.role) }}</strong>
                                        <span v-if="g.psgc_code">· PSGC {{ g.psgc_code }}</span>
                                        <span v-if="g.project_id">· Proj #{{ g.project_id }}</span>
                                        <button @click="deleteGrant(g.id)" style="margin-left: 4px; opacity: 0.7; hover:opacity: 1;" title="Revoke grant">×</button>
                                    </span>
                                    <span v-if="user.role_grants.length === 0" style="font-size: 0.78rem; color: var(--c-ink-muted); font-style: italic;">
                                        No grants assigned
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="chip-status" :class="user.is_active ? 'status-online' : 'status-offline'">
                                    <span class="status-dot"></span>
                                    <span>{{ user.is_active ? 'Active' : 'Deactivated' }}</span>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.4rem;">
                                    <button class="btn btn-outline btn-sm" @click="openGrantModal(user)">
                                        + Grant
                                    </button>
                                    <button class="btn btn-ghost btn-sm" @click="toggleActive(user)">
                                        {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal: Create User -->
        <div v-if="showCreateModal" class="modal-backdrop" @click.self="showCreateModal = false">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="card-title">Add Regional User</h3>
                    <button class="btn btn-ghost btn-sm" @click="showCreateModal = false">✕</button>
                </div>
                <form @submit.prevent="submitCreateUser">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" v-model="createForm.name" class="form-input" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" v-model="createForm.email" class="form-input" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <input type="password" v-model="createForm.password" class="form-input" required minlength="8" />
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Agency / Operating Unit</label>
                                <input type="text" v-model="createForm.agency_unit" class="form-input" placeholder="e.g. DICT Isabela / NIPPSB" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" v-model="createForm.phone" class="form-input" placeholder="e.g. +63 917 123 4567" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="createForm.processing">Create User</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Assign Role Grant -->
        <div v-if="selectedUserForGrant" class="modal-backdrop" @click.self="selectedUserForGrant = null">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="card-title">Assign Scoped Grant to {{ selectedUserForGrant.name }}</h3>
                    <button class="btn btn-ghost btn-sm" @click="selectedUserForGrant = null">✕</button>
                </div>
                <form @submit.prevent="submitGrant">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Role <span class="required">*</span></label>
                            <select v-model="grantForm.role" class="form-select" required>
                                <option v-for="(label, key) in roles" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Geographic PSGC Scope (Optional)</label>
                            <input 
                                type="text" 
                                v-model="grantForm.psgc_code" 
                                class="form-input" 
                                placeholder="e.g. 0231 (Isabela), 0215 (Cagayan) or leave blank for Region 2 wide" 
                            />
                            <div class="form-help">Leave blank for region-wide access. Enter province or LGU PSGC prefix to restrict access.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Project ID Scope (Optional)</label>
                            <input 
                                type="number" 
                                v-model="grantForm.project_id" 
                                class="form-input" 
                                placeholder="Leave blank for all projects in scope" 
                            />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="selectedUserForGrant = null">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="grantForm.processing">Assign Grant</button>
                    </div>
                </form>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    users: Array,
    roles: Object,
});

const showCreateModal = ref(false);
const selectedUserForGrant = ref(null);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    agency_unit: '',
    phone: '',
});

const grantForm = useForm({
    role: 'encoder',
    psgc_code: '',
    project_id: null,
});

function formatRole(role) {
    return props.roles[role] || role;
}

function submitCreateUser() {
    createForm.post('/admin/users', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function openGrantModal(user) {
    selectedUserForGrant.value = user;
    grantForm.role = 'encoder';
    grantForm.psgc_code = '';
    grantForm.project_id = null;
}

function submitGrant() {
    if (!selectedUserForGrant.value) return;
    grantForm.post(`/admin/users/${selectedUserForGrant.value.id}/grants`, {
        onSuccess: () => {
            selectedUserForGrant.value = null;
            grantForm.reset();
        },
    });
}

function deleteGrant(grantId) {
    if (!confirm('Are you sure you want to revoke this grant?')) return;
    router.delete(`/admin/grants/${grantId}`);
}

function toggleActive(user) {
    router.post(`/admin/users/${user.id}/toggle-active`);
}
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
    max-width: 520px;
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
    gap: 0.65rem;
}
</style>
