<template>
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Brand Header -->
            <div class="auth-header">
                <div class="brand-badge-large">NMS·V2</div>
                <h1 class="auth-title">DICT Region 2</h1>
                <p class="auth-subtitle">Project & Infrastructure Rollout Tracker</p>
            </div>

            <!-- Error Banner -->
            <div v-if="form.errors.email || form.errors.password" class="auth-alert">
                <span>{{ form.errors.email || form.errors.password }}</span>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="auth-form">
                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <input 
                        type="email" 
                        v-model="form.email" 
                        class="form-input" 
                        placeholder="user@dict.gov.ph" 
                        required 
                        autofocus 
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        v-model="form.password" 
                        class="form-input" 
                        placeholder="••••••••••••" 
                        required 
                    />
                </div>

                <div class="auth-options">
                    <label class="remember-label">
                        <input type="checkbox" v-model="form.remember" />
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                    <span v-if="form.processing">Authenticating...</span>
                    <span v-else>Sign In to Platform</span>
                </button>
            </form>

            <div class="auth-footer">
                <span>Government of the Philippines • Region 02</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<style scoped>
.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--c-bg);
    padding: 1.5rem;
}

.auth-card {
    width: 100%;
    max-width: 440px;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2.5rem 2.25rem;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.brand-badge-large {
    display: inline-block;
    background: var(--c-primary);
    color: #ffffff;
    font-weight: 700;
    font-size: 1.15rem;
    padding: 0.45rem 1rem;
    border-radius: var(--radius-sm);
    letter-spacing: -0.02em;
    margin-bottom: 0.85rem;
}

.auth-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--c-ink);
}

.auth-subtitle {
    font-size: 0.85rem;
    color: var(--c-ink-muted);
    margin-top: 0.25rem;
}

.auth-alert {
    background: var(--scale-danger-bg);
    border: 1px solid var(--scale-danger-border);
    color: var(--scale-danger);
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    margin-bottom: 1.25rem;
}

.auth-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--c-ink-secondary);
    cursor: pointer;
}

.btn-block {
    width: 100%;
    padding: 0.75rem;
    font-size: 0.95rem;
}

.auth-footer {
    margin-top: 2rem;
    text-align: center;
    font-size: 0.75rem;
    font-family: var(--font-mono);
    color: var(--c-ink-muted);
    border-top: 1px solid var(--c-border-subtle);
    padding-top: 1.25rem;
}
</style>
