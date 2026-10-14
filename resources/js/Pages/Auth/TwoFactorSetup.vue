<template>
    <AppShell>
        <div style="max-width: 680px; margin: 0 auto;">
            <div class="page-header" style="margin-bottom: 1.25rem;">
                <div class="page-title-group">
                    <h1>Two-Factor Authentication Security</h1>
                    <div class="page-subtitle">
                        Required security compliance for Super Admins, Project Managers, and Reviewers
                    </div>
                </div>
            </div>

            <!-- Warning Notice if required -->
            <div v-if="$page.props.flash?.warning" class="card" style="margin-bottom: 1.25rem; background: var(--scale-warning-bg); border-color: var(--scale-warning-border); padding: 1rem 1.25rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--scale-warning)" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--c-ink);">2FA Setup Mandated</div>
                        <div style="font-size: 0.82rem; color: var(--c-ink-secondary); margin-top: 0.2rem;">
                            {{ $page.props.flash.warning }}
                        </div>
                        <button class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; margin-top: 0.6rem; border-color: var(--scale-warning-border); background: var(--c-surface);" @click="bypassDemo">
                            Proceed to Dashboard (Demo Mode) →
                        </button>
                    </div>
                </div>
            </div>

            <!-- State 1: 2FA is Fully Enabled -->
            <div v-if="confirmed" class="card" style="padding: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 44px; height: 44px; border-radius: var(--radius-full); background: var(--scale-success-bg); border: 1px solid var(--scale-success-border); color: var(--scale-success); display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 1.1rem; color: var(--c-ink);">Two-Factor Authentication is Active</div>
                        <div style="font-size: 0.82rem; color: var(--c-ink-muted);">Your account is protected with a time-based one-time password (TOTP).</div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <Link href="/" class="btn btn-primary">Return to Live Dashboard</Link>
                    <button class="btn btn-secondary" @click="fetchRecoveryCodes">View Recovery Codes</button>
                    <button class="btn btn-danger" @click="disable2FA" style="margin-left: auto;">Disable 2FA</button>
                </div>

                <!-- Recovery Codes Modal/Box -->
                <div v-if="recoveryCodes.length > 0" style="margin-top: 1.5rem; padding: 1.25rem; background: var(--c-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--c-border);">
                    <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.5rem;">Emergency Recovery Codes</div>
                    <p style="font-size: 0.8rem; color: var(--c-ink-muted); margin-bottom: 0.75rem;">Store these safe emergency codes in case you lose access to your authenticator app:</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-family: var(--font-mono); font-size: 0.85rem;">
                        <div v-for="c in recoveryCodes" :key="c" style="background: var(--c-surface); padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); border: 1px solid var(--c-border);">
                            {{ c }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- State 2: 2FA Setup Flow -->
            <div v-else class="card" style="padding: 1.75rem;">
                <!-- Step A: Enable Request -->
                <div v-if="!qrCodeSvg">
                    <h3 style="margin-bottom: 0.5rem;">Protect Your Regional Access</h3>
                    <p style="font-size: 0.85rem; color: var(--c-ink-secondary); line-height: 1.5; margin-bottom: 1.5rem;">
                        Two-factor authentication adds an extra layer of security by requiring a 6-digit verification code from Google Authenticator, Microsoft Authenticator, or another compatible app each time you log in.
                    </p>
                    <button class="btn btn-primary" @click="enable2FA" :disabled="loading">
                        <span v-if="loading">Generating Security Keys...</span>
                        <span v-else>Begin 2FA Enrollment</span>
                    </button>
                </div>

                <!-- Step B: Scan QR & Confirm -->
                <div v-else>
                    <h3 style="margin-bottom: 0.5rem;">Scan QR Code</h3>
                    <p style="font-size: 0.85rem; color: var(--c-ink-secondary); margin-bottom: 1.25rem;">
                        Open your authenticator app and scan this barcode, or manually enter the setup key:
                    </p>

                    <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                        <div v-html="qrCodeSvg" style="background: #ffffff; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--c-border); width: 180px; height: 180px;"></div>
                        <div style="flex: 1; min-width: 220px;">
                            <div class="form-label" style="font-size: 0.78rem; text-transform: uppercase; font-family: var(--font-mono);">Setup Key:</div>
                            <div style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 600; background: var(--c-surface-subtle); padding: 0.6rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--c-border); word-break: break-all; margin-top: 0.35rem;">
                                {{ secretKey }}
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="confirm2FA" style="border-top: 1px solid var(--c-border-subtle); padding-top: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Enter 6-Digit Code from App to Confirm <span class="required">*</span></label>
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                v-model="confirmCode" 
                                class="form-input" 
                                style="font-family: var(--font-mono); font-size: 1.2rem; letter-spacing: 0.2em; max-width: 200px;" 
                                placeholder="000 000" 
                                maxlength="6" 
                                required 
                            />
                            <div v-if="confirmError" class="form-error" style="margin-top: 0.35rem;">{{ confirmError }}</div>
                        </div>

                        <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" :disabled="confirming">
                                <span v-if="confirming">Confirming...</span>
                                <span v-else>Confirm & Activate 2FA</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    enabled: Boolean,
    confirmed: Boolean,
});

const loading = ref(false);
const confirming = ref(false);
const qrCodeSvg = ref('');
const secretKey = ref('');
const confirmCode = ref('');
const confirmError = ref('');
const recoveryCodes = ref([]);

function bypassDemo() {
    router.post('/two-factor/demo-bypass');
}

async function enable2FA() {
    loading.value = true;
    try {
        await axios.post('/user/two-factor-authentication');
        const [qrRes, secretRes] = await Promise.all([
            axios.get('/user/two-factor-qr-code'),
            axios.get('/user/two-factor-secret-key'),
        ]);
        qrCodeSvg.value = qrRes.data.svg;
        secretKey.value = secretRes.data.secretKey;
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

async function confirm2FA() {
    confirming.value = true;
    confirmError.value = '';
    try {
        await axios.post('/user/confirmed-two-factor-authentication', {
            code: confirmCode.value,
        });
        router.reload();
    } catch (err) {
        confirmError.value = err.response?.data?.message || 'Invalid 6-digit confirmation code. Please check your app.';
    } finally {
        confirming.value = false;
    }
}

async function fetchRecoveryCodes() {
    try {
        const res = await axios.get('/user/two-factor-recovery-codes');
        recoveryCodes.value = res.data;
    } catch (err) {
        console.error(err);
    }
}

async function disable2FA() {
    if (!confirm('Are you sure you want to disable 2FA? Privileged roles will be required to re-enable it.')) return;
    try {
        await axios.delete('/user/two-factor-authentication');
        router.reload();
    } catch (err) {
        console.error(err);
    }
}
</script>
