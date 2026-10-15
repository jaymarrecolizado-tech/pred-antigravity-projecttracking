<template>
    <AppShell>
        <!-- Clean Hero Welcome Header -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1.35rem 1.6rem; background: linear-gradient(135deg, var(--c-surface), var(--c-surface-subtle)); border-left: 4px solid var(--c-primary);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <span class="badge badge-kind-a" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em; padding: 0.2rem 0.55rem;">DICT REGION 02</span>
                        <span style="font-size: 0.76rem; font-family: var(--font-mono); color: var(--c-ink-muted);">FREE WI-FI FOR ALL PROJECT</span>
                        <span class="chip-status status-online" style="margin-left: 0.35rem; font-size: 0.72rem;">
                            <span class="status-dot"></span>
                            <span>Live Telemetry</span>
                        </span>
                    </div>
                    <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--c-ink); margin: 0; letter-spacing: -0.02em;">
                        Free Wi-Fi Project Operations & Rollout
                    </h1>
                    <div style="font-size: 0.84rem; color: var(--c-ink-muted); margin-top: 0.25rem;">
                        Tracking {{ summary.total_sites }} Free Wi-Fi access sites across 5 provinces (Batanes, Cagayan, Isabela, Nueva Vizcaya, Quirino)
                    </div>
                </div>

                <!-- Primary Action Buttons -->
                <div style="display: flex; gap: 0.65rem; align-items: center;">
                    <Link href="/submissions/create" class="btn btn-secondary" style="font-size: 0.84rem; padding: 0.5rem 0.95rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        <span>New Field Report</span>
                    </Link>
                    <Link href="/map" class="btn btn-primary" style="font-size: 0.84rem; padding: 0.5rem 1.05rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon>
                            <line x1="9" x2="9" y1="3" y2="18"></line>
                            <line x1="15" x2="15" y1="6" y2="21"></line>
                        </svg>
                        <span>Explore Free Wi-Fi Map</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- 3 Core Hero KPI Cards -->
        <div class="grid-3" style="margin-bottom: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Program Scope</span>
                    <span class="badge badge-kind-a">Region 02</span>
                </div>
                <div class="metric-value" style="font-size: 1.45rem;">Free Wi-Fi for All</div>
                <div class="metric-subtext">Delivery lots & contracts (PICS, PIALEOS, PoP, SINAG)</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Operational Wi-Fi Sites</span>
                    <span style="color: var(--scale-success); font-weight: 700; font-size: 0.85rem; font-family: var(--font-mono);">
                        {{ summary.completion_rate }}% Online
                    </span>
                </div>
                <div class="metric-value">
                    {{ summary.operational_sites }} 
                    <span style="font-size: 0.95rem; font-weight: 500; color: var(--c-ink-muted);">/ {{ summary.total_sites }} Sites</span>
                </div>
                <div class="metric-subtext">Average activation cycle time: {{ summary.average_cycle_time }} days</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Municipal Coverage</span>
                    <span class="badge badge-kind-b">{{ summary.scope_coverage.coverage_pct }}% Covered</span>
                </div>
                <div class="metric-value">{{ summary.scope_coverage.covered_lgus }} / {{ summary.scope_coverage.total_lgus }}</div>
                <div class="metric-subtext">LGUs with active Free Wi-Fi connectivity</div>
            </div>
        </div>

        <!-- Clean Segmented Tabs Navigation -->
        <div class="tabs-nav" style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--c-border); display: flex; gap: 0.5rem;">
            <button 
                type="button" 
                class="tab-btn" 
                :class="{ active: activeTab === 'progress' }"
                @click="activeTab = 'progress'"
                style="padding: 0.6rem 1rem; font-size: 0.85rem; font-weight: 600;"
            >
                📊 Rollout & Delivery Lots
            </button>
            <button 
                type="button" 
                class="tab-btn" 
                :class="{ active: activeTab === 'provinces' }"
                @click="activeTab = 'provinces'"
                style="padding: 0.6rem 1rem; font-size: 0.85rem; font-weight: 600;"
            >
                🏛️ Provinces & LGUs
            </button>
            <button 
                type="button" 
                class="tab-btn" 
                :class="{ active: activeTab === 'activity' }"
                @click="activeTab = 'activity'"
                style="padding: 0.6rem 1rem; font-size: 0.85rem; font-weight: 600;"
            >
                📋 Field Updates & Alerts
                <span v-if="backlog.total_backlog > 0" class="badge" style="background: rgba(239, 68, 68, 0.15); color: var(--scale-danger); font-size: 0.68rem; margin-left: 0.4rem;">
                    {{ backlog.total_backlog }} Overdue
                </span>
            </button>
        </div>

        <!-- TAB 1: IMPLEMENTATION PROGRESS & PROJECTS -->
        <div v-if="activeTab === 'progress'">
            <!-- Trajectory vs Milestone -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <div>
                        <div class="card-title">Rollout Trajectory & Monthly Velocity</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Monthly activated Free Wi-Fi sites vs planned targets
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem; font-family: var(--font-mono);">
                        <span style="display: flex; align-items: center; gap: 0.3rem;">
                            <span style="width: 10px; height: 10px; background: var(--scale-success); border-radius: 2px;"></span> Actual Activated
                        </span>
                        <span style="display: flex; align-items: center; gap: 0.3rem;">
                            <span style="width: 10px; height: 10px; background: var(--c-border-hover); border-radius: 2px;"></span> Target Plan
                        </span>
                    </div>
                </div>
                <div class="card-body" style="padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; height: 160px; padding-top: 1rem; border-bottom: 1px solid var(--c-border);">
                        <div v-for="(label, idx) in trajectory.labels" :key="label"
                             style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.4rem; height: 100%; justify-content: flex-end;">
                            <div style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--c-ink); font-weight: 600;">
                                {{ trajectory.actual[idx] }}
                            </div>
                            <div style="display: flex; gap: 4px; align-items: flex-end; height: 110px; width: 36px; justify-content: center;">
                                <div :style="{ height: getBarHeight(trajectory.planned[idx]) + '%', width: '12px', background: 'var(--c-border-hover)', borderRadius: '2px 2px 0 0' }"
                                     title="Planned Target"></div>
                                <div :style="{ height: getBarHeight(trajectory.actual[idx]) + '%', width: '12px', background: 'var(--scale-success)', borderRadius: '2px 2px 0 0' }"
                                     title="Actual Activated"></div>
                            </div>
                            <div style="font-size: 0.7rem; font-family: var(--font-mono); color: var(--c-ink-muted); white-space: nowrap;">
                                {{ label.split(' ')[0] }}
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.75rem;">
                        <span>Site activations progressing across all 5 provinces in Region 2</span>
                        <span style="font-family: var(--font-mono); color: var(--scale-success); font-weight: 600;">Velocity: Steady</span>
                    </div>
                </div>
            </div>

            <!-- Priority Delivery Lots Table -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <div>
                        <div class="card-title">Free Wi-Fi Delivery Lots & Contracts</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Live status and completion across procurement lots and batches
                        </div>
                    </div>
                    <Link href="/projects" class="btn btn-outline btn-sm">View All Lots & Contracts →</Link>
                </div>
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Delivery Lot / Contract</th>
                                <th>Category</th>
                                <th>Accomplishment Rate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="proj in priorityProjects" :key="proj.id">
                                <td>
                                    <div style="font-weight: 600; color: var(--c-ink);">{{ proj.name }}</div>
                                    <div style="font-size: 0.72rem; font-family: var(--font-mono); color: var(--c-ink-muted);">{{ proj.code }} • {{ proj.sites_count }} sites</div>
                                </td>
                                <td>
                                    <span class="badge badge-kind-a">Free Wi-Fi</span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                                        <span style="font-family: var(--font-mono); font-weight: 600; font-size: 0.82rem; width: 42px;">{{ proj.percent }}%</span>
                                        <div style="width: 100px; height: 6px; background: var(--c-border); border-radius: var(--radius-full); overflow: hidden;">
                                            <div :style="{ width: proj.percent + '%', background: getProgressColor(proj.percent), height: '100%' }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <Link :href="'/map?project_id=' + proj.id" class="btn btn-ghost btn-sm" style="font-size: 0.76rem;">View On Map →</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: PROVINCES & LGU BREAKDOWN -->
        <div v-else-if="activeTab === 'provinces'">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Provincial Rollup Leaderboard -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Provincial Rollup Leaderboard</div>
                            <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                                Live completion rate across Region 2 provinces
                            </div>
                        </div>
                        <span class="badge badge-neutral" style="font-family: var(--font-mono);">5 Provinces</span>
                    </div>
                    <div class="card-body" style="padding: 1.25rem;">
                        <div style="display: flex; flex-direction: column; gap: 1.15rem;">
                            <div v-for="prov in provincialLeaderboard" :key="prov.psgc"
                                 style="padding-bottom: 0.85rem; border-bottom: 1px solid var(--c-border-subtle);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <strong style="color: var(--c-ink); font-size: 0.92rem;">{{ prov.name }}</strong>
                                        <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--c-ink-muted);">
                                            {{ prov.completed_sites }}/{{ prov.total_sites }} sites
                                        </span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <span v-if="prov.backlog_sites > 0"
                                              style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--scale-danger); background: rgba(239, 68, 68, 0.1); padding: 0.1rem 0.35rem; border-radius: var(--radius-sm);">
                                            {{ prov.backlog_sites }} backlog
                                        </span>
                                        <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.88rem;"
                                              :style="{ color: getProgressColor(prov.completion_rate) }">
                                            {{ prov.completion_rate }}%
                                        </span>
                                    </div>
                                </div>
                                <div style="height: 6px; background: var(--c-border); border-radius: var(--radius-full); overflow: hidden;">
                                    <div :style="{ width: prov.completion_rate + '%', background: getProgressColor(prov.completion_rate), height: '100%', transition: 'width 0.4s ease' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LGU & Beneficiaries Demographics -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Scope Coverage & Beneficiaries</div>
                            <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                                Municipal reach & GAD gender-disaggregated counts
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="padding: 1rem; border-radius: var(--radius-md); background: var(--c-surface-subtle); border: 1px solid var(--c-border);">
                            <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: var(--c-ink-muted); margin-bottom: 0.35rem;">
                                LGU Scope Reach
                            </div>
                            <div style="font-size: 1.35rem; font-weight: 800; color: var(--c-ink);">
                                {{ summary.scope_coverage.covered_lgus }} of {{ summary.scope_coverage.total_lgus }} LGUs
                            </div>
                            <div style="font-size: 0.78rem; color: var(--c-ink-secondary); margin-top: 0.2rem;">
                                {{ summary.scope_coverage.coverage_pct }}% of all cities & municipalities in Region 2 have active deployments.
                            </div>
                        </div>

                        <div style="padding: 1rem; border-radius: var(--radius-md); background: var(--c-surface-subtle); border: 1px solid var(--c-border);">
                            <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: var(--c-ink-muted); margin-bottom: 0.35rem;">
                                Total Beneficiaries (GAD)
                            </div>
                            <div style="font-size: 1.35rem; font-weight: 800; color: var(--c-ink);">
                                {{ formatNumber(gad.total_beneficiaries) }} People Reached
                            </div>
                            <div style="margin-top: 0.6rem; display: flex; gap: 0.75rem;">
                                <div style="flex: 1; padding: 0.5rem; background: var(--c-surface); border-radius: var(--radius-sm); border: 1px solid var(--c-border-subtle);">
                                    <div style="font-size: 0.7rem; color: var(--c-ink-muted);">Female</div>
                                    <strong style="color: #ec4899; font-size: 0.95rem;">{{ formatNumber(gad.female_count) }} ({{ gad.female_percentage }}%)</strong>
                                </div>
                                <div style="flex: 1; padding: 0.5rem; background: var(--c-surface); border-radius: var(--radius-sm); border: 1px solid var(--c-border-subtle);">
                                    <div style="font-size: 0.7rem; color: var(--c-ink-muted);">Male</div>
                                    <strong style="color: #3b82f6; font-size: 0.95rem;">{{ formatNumber(gad.male_count) }} ({{ (100 - gad.female_percentage).toFixed(1) }}%)</strong>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <Link href="/admin/geo" class="btn btn-outline btn-sm">Explore PSGC Hierarchy Tree →</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: FIELD UPDATES & ALERTS -->
        <div v-else-if="activeTab === 'activity'">
            <!-- Backlog Alert Card -->
            <div v-if="backlog.total_backlog > 0" class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--scale-danger); padding: 1.15rem 1.4rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-weight: 800; font-size: 1rem; color: var(--c-ink);">Contractual Overdue Backlog</span>
                            <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: var(--scale-danger); font-family: var(--font-mono); font-weight: 700;">
                                {{ backlog.total_backlog }} Sites
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--c-ink-muted); margin-top: 0.25rem;">
                            Deployments exceeding contractual target completion dates grouped by aging delinquency
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <div v-for="bucket in backlog.buckets" :key="bucket.label"
                             style="padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); font-size: 0.75rem; font-family: var(--font-mono); display: flex; align-items: center; gap: 0.4rem;"
                             :style="getBucketStyle(bucket.severity)">
                            <span>{{ bucket.label }}:</span>
                            <strong style="font-size: 0.85rem;">{{ bucket.count }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Verified Field Reports Table -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Recent Field Telemetry & Verification</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Chronological milestones submitted by field engineers
                        </div>
                    </div>
                    <Link href="/reviews" class="btn btn-outline btn-sm">Open Verification Queue →</Link>
                </div>
                <div class="card-body" style="padding: 0.75rem 1.25rem;">
                    <div v-if="recentInterventions.length === 0" style="padding: 2.5rem; text-align: center; color: var(--c-ink-muted);">
                        No recent field interventions recorded yet.
                    </div>
                    <div v-else style="display: flex; flex-direction: column;">
                        <div v-for="inv in recentInterventions" :key="inv.id"
                             style="padding: 0.85rem 0; border-bottom: 1px solid var(--c-border-subtle); display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-family: var(--font-mono); font-size: 0.82rem; font-weight: 700; color: var(--c-ink);">{{ inv.site_code }}</span>
                                    <span class="badge" :class="getVerificationBadgeClass(inv.verification_status)">{{ inv.verification_status }}</span>
                                </div>
                                <div style="font-size: 0.82rem; color: var(--c-ink); margin-top: 0.2rem;">
                                    {{ inv.intervention_type }} • <span style="color: var(--c-ink-muted);">{{ inv.location_name }}</span>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--c-ink-muted); font-family: var(--font-mono); margin-top: 0.15rem;">
                                    By {{ inv.author }} • {{ inv.occurred_at }}
                                </div>
                            </div>
                            <span class="chip-status" :class="inv.new_status === 'operational' ? 'status-online' : 'status-ongoing'">
                                <span class="status-dot"></span>
                                <span>{{ inv.new_status }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    backlog: {
        type: Object,
        required: true,
    },
    provincialLeaderboard: {
        type: Array,
        required: true,
    },
    gad: {
        type: Object,
        required: true,
    },
    trajectory: {
        type: Object,
        required: true,
    },
    archetypeStats: {
        type: Object,
        required: true,
    },
    priorityProjects: {
        type: Array,
        required: true,
    },
    recentInterventions: {
        type: Array,
        required: true,
    },
});

const activeTab = ref('progress');
const currentTime = ref('');

onMounted(() => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

function formatNumber(num) {
    if (!num) return '0';
    return Number(num).toLocaleString();
}

function getProgressColor(pct) {
    if (pct <= 20) return 'var(--scale-danger)';
    if (pct <= 70) return 'var(--scale-warning)';
    return 'var(--scale-success)';
}

function getBucketStyle(severity) {
    switch (severity) {
        case 'critical':
            return { background: 'rgba(239, 68, 68, 0.15)', color: '#ef4444', border: '1px solid rgba(239, 68, 68, 0.3)' };
        case 'high':
            return { background: 'rgba(249, 115, 22, 0.15)', color: '#f97316', border: '1px solid rgba(249, 115, 22, 0.3)' };
        case 'medium':
            return { background: 'rgba(245, 158, 11, 0.15)', color: '#f59e0b', border: '1px solid rgba(245, 158, 11, 0.3)' };
        default:
            return { background: 'var(--c-surface-hover)', color: 'var(--c-ink)', border: '1px solid var(--c-border)' };
    }
}

function getVerificationBadgeClass(status) {
    switch (status) {
        case 'verified': return 'badge-success';
        case 'flagged': return 'badge-danger';
        case 'rejected': return 'badge-neutral';
        default: return 'badge-warning';
    }
}

function getBarHeight(val) {
    const maxVal = Math.max(...props.trajectory.planned, ...props.trajectory.actual, 1);
    return Math.max(12, Math.min(100, Math.round((val / maxVal) * 100)));
}
</script>
