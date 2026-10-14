<template>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="security-icon-circle">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1 class="auth-title">Two-Factor Authentication</h1>
                <p class="auth-subtitle">
                    {{ recovery ? 'Enter one of your emergency recovery codes.' : 'Please confirm access using your authenticator app.' }}
                </p>
            </div>

            <!-- Error Banner -->
            <div v-if="form.errors.code || form.errors.recovery_code" class="auth-alert">
                <span>{{ form.errors.code || form.errors.recovery_code }}</span>
            </div>

            <form @submit.prevent="submit" class="auth-form">
                <div v-if="!recovery" class="form-group">
                    <label class="form-label">6-Digit Security Code</label>
                    <input 
                        type="text" 
                        inputmode="numeric" 
                        v-model="form.code" 
                        class="form-input code-input" 
                        placeholder="000 000" 
                        maxlength="7" 
                        autofocus 
                        required 
                    />
                </div>

                <div v-else class="form-group">
                    <label class="form-label">Emergency Recovery Code</label>
                    <input 
                        type="text" 
                        v-model="form.recovery_code" 
                        class="form-input" 
                        placeholder="abcdef-98765" 
                        autofocus 
                        required 
                    />
                </div>

                <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                    <span v-if="form.processing">Verifying...</span>
                    <span v-else>Verify & Proceed</span>
                </button>

                <div style="text-align: center; margin-top: 1.25rem;">
                    <button type="button" class="btn btn-ghost btn-sm" @click="toggleRecovery">
                        {{ recovery ? 'Use Authenticator Code instead' : 'Use an Emergency Recovery Code' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

function toggleRecovery() {
    recovery.value = !recovery.value;
    form.clearErrors();
    form.code = '';
    form.recovery_code = '';
}

function submit() {
    form.post('/two-factor-challenge');
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

.security-icon-circle {
    width: 56px;
    height: 56px;
    margin: 0 auto 1.25rem auto;
    border-radius: var(--radius-full);
    background: var(--c-primary-subtle);
    color: var(--c-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--c-primary-border);
}

.auth-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--c-ink);
}

.auth-subtitle {
    font-size: 0.85rem;
    color: var(--c-ink-muted);
    margin-top: 0.35rem;
    line-height: 1.4;
}

.code-input {
    text-align: center;
    font-family: var(--font-mono);
    font-size: 1.4rem;
    letter-spacing: 0.3em;
    font-weight: 600;
    padding: 0.65rem;
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

.btn-block {
    width: 100%;
    padding: 0.75rem;
    font-size: 0.95rem;
}
</style>
