<template>
    <div :class="['recon-brand-lockup', `size-${size}`, variant]">
        <!-- Vector Emblem -->
        <div class="recon-emblem-wrapper">
            <svg class="recon-emblem-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient :id="`grad-${uid}`" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1d4ed8" />
                        <stop offset="50%" stop-color="#0284c7" />
                        <stop offset="100%" stop-color="#10b981" />
                    </linearGradient>
                    <linearGradient :id="`glow-${uid}`" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" />
                        <stop offset="100%" stop-color="#34d399" />
                    </linearGradient>
                    <filter :id="`shadow-${uid}`" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#0284c7" flood-opacity="0.3" />
                    </filter>
                </defs>

                <!-- Hexagonal Tactical Chassis -->
                <polygon 
                    points="32,4 58,18.5 58,45.5 32,60 6,45.5 6,18.5" 
                    class="recon-chassis"
                    :stroke="`url(#grad-${uid})`" 
                    stroke-width="3" 
                    stroke-linejoin="round"
                    :filter="`url(#shadow-${uid})`"
                />

                <!-- Coordinate Grid Crosshairs -->
                <circle cx="32" cy="32" r="18" :stroke="`url(#grad-${uid})`" stroke-width="1.2" stroke-dasharray="3 3" opacity="0.65" />
                <circle cx="32" cy="32" r="23" stroke="currentColor" stroke-width="0.8" opacity="0.25" />

                <!-- Reticle Radar Axis Markers -->
                <line x1="32" y1="9" x2="32" y2="15" :stroke="`url(#glow-${uid})`" stroke-width="2" stroke-linecap="round" />
                <line x1="32" y1="49" x2="32" y2="55" :stroke="`url(#glow-${uid})`" stroke-width="2" stroke-linecap="round" />
                <line x1="10" y1="32" x2="16" y2="32" :stroke="`url(#glow-${uid})`" stroke-width="2" stroke-linecap="round" />
                <line x1="48" y1="32" x2="54" y2="32" :stroke="`url(#glow-${uid})`" stroke-width="2" stroke-linecap="round" />

                <!-- Stylized Futuristic Monogram 'R' -->
                <path 
                    d="M24 20 H33 C36.5 20 38.5 22.5 38.5 25.5 C38.5 28.5 36.5 31 33 31 H28 V44 M28 31 L38 44" 
                    class="recon-r-body"
                    stroke="#ffffff" 
                    stroke-width="3.6" 
                    stroke-linecap="round" 
                    stroke-linejoin="round"
                />
                <line x1="24" y1="20" x2="24" y2="44" :stroke="`url(#glow-${uid})`" stroke-width="3.6" stroke-linecap="round" />

                <!-- Telemetry Beacon Point -->
                <circle cx="38.5" cy="25.5" r="2.4" fill="#10b981" />
                <circle cx="32" cy="4" r="2" fill="#38bdf8" />
            </svg>
        </div>

        <!-- Typography Wordmark -->
        <div v-if="variant !== 'emblem'" class="recon-text-group">
            <div class="recon-title-row">
                <span class="recon-logotype">RECON</span>
                <span class="recon-version-pill">R2</span>
            </div>
            <div class="recon-subtext">
                {{ subtitle || 'Regional Operations Network' }}
            </div>
            <div v-if="withGovBadge" class="recon-gov-badge">
                <span class="gov-dot"></span>
                <span>DICT REGION 02</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    size: {
        type: String,
        default: 'md', // 'sm', 'md', 'lg', 'xl'
    },
    variant: {
        type: String,
        default: 'full', // 'full', 'emblem', 'compact'
    },
    subtitle: {
        type: String,
        default: '',
    },
    withGovBadge: {
        type: Boolean,
        default: false,
    },
});

const uid = computed(() => Math.random().toString(36).substring(2, 7));
</script>

<style scoped>
.recon-brand-lockup {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    user-select: none;
    text-decoration: none;
}

.recon-emblem-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.recon-emblem-svg {
    display: block;
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.15));
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.recon-brand-lockup:hover .recon-emblem-svg {
    transform: scale(1.04);
}

.recon-chassis {
    fill: #090d16;
}

[data-theme="light"] .recon-chassis {
    fill: #0f172a;
}

/* Sizing scales */
.size-sm .recon-emblem-wrapper {
    width: 28px;
    height: 28px;
}
.size-sm .recon-logotype {
    font-size: 1rem;
}
.size-sm .recon-subtext {
    font-size: 0.64rem;
}

.size-md .recon-emblem-wrapper {
    width: 38px;
    height: 38px;
}
.size-md .recon-logotype {
    font-size: 1.25rem;
}
.size-md .recon-subtext {
    font-size: 0.72rem;
}

.size-lg .recon-emblem-wrapper {
    width: 52px;
    height: 52px;
}
.size-lg .recon-logotype {
    font-size: 1.65rem;
}
.size-lg .recon-subtext {
    font-size: 0.8rem;
}

.size-xl .recon-emblem-wrapper {
    width: 72px;
    height: 72px;
}
.size-xl .recon-logotype {
    font-size: 2.2rem;
}
.size-xl .recon-subtext {
    font-size: 0.88rem;
}

/* Text Group Typography */
.recon-text-group {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.recon-title-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.recon-logotype {
    font-family: var(--font-sans);
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--c-ink);
    background: linear-gradient(135deg, var(--c-ink) 30%, var(--c-primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.recon-version-pill {
    font-family: var(--font-mono);
    font-size: 0.62rem;
    font-weight: 700;
    padding: 0.12rem 0.38rem;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, #1d4ed8, #0284c7);
    color: #ffffff;
    letter-spacing: 0.04em;
    line-height: 1;
}

.recon-subtext {
    font-family: var(--font-mono);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.68rem;
    color: var(--c-ink-muted);
    font-weight: 600;
    margin-top: 2px;
}

.recon-gov-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.62rem;
    font-family: var(--font-mono);
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--c-primary);
    margin-top: 0.25rem;
}

.gov-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
}
</style>
