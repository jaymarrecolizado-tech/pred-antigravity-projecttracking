<template>
    <AppShell>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-group">
                <h1>Regional Infrastructure & Program Rollout</h1>
                <div class="page-subtitle">
                    Live implementation and status telemetry across Region 2 (Cagayan Valley)
                </div>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <Link href="/submissions/create" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    <span>New Field Report</span>
                </Link>
                <Link href="/map" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon>
                        <line x1="9" x2="9" y1="3" y2="18"></line>
                        <line x1="15" x2="15" y1="6" y2="21"></line>
                    </svg>
                    <span>Launch Interactive Map</span>
                </Link>
            </div>
        </div>

        <!-- Live Status Bar (Matching NMS-V2 Reference) -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                    <div style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--c-ink);">
                        <span style="color: var(--c-ink-muted); font-weight: 500;">TRACKED:</span> {{ summary.total_sites }} SITES
                    </div>
                    <div class="chip-status status-online">
                        <span class="status-dot"></span>
                        <span style="font-family: var(--font-mono); font-weight: 600;">OPERATIONAL {{ summary.operational_sites }}</span>
                    </div>
                    <div class="chip-status status-warning" v-if="summary.offline_sites > 0">
                        <span class="status-dot"></span>
                        <span style="font-family: var(--font-mono); font-weight: 600;">IN PROGRESS / OFFLINE {{ summary.offline_sites }}</span>
                    </div>
                    <div class="chip-status status-danger" v-if="backlog.total_backlog > 0">
                        <span class="status-dot"></span>
                        <span style="font-family: var(--font-mono); font-weight: 600;">BACKLOG {{ backlog.total_backlog }}</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.78rem; font-family: var(--font-mono); color: var(--c-ink-muted);">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--scale-success); animation: pulse 2s infinite;"></span>
                    <span>Refreshed: {{ currentTime }} • Region 02 Rollup Engine</span>
                </div>
            </div>
        </div>

        <!-- 4-Stat Metric Row -->
        <div class="grid-4" style="margin-bottom: 1.5rem;">
            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Active Initiatives</span>
                    <span class="badge badge-kind-a">Portfolio</span>
                </div>
                <div class="metric-value">{{ summary.total_projects }}</div>
                <div class="metric-subtext">Across 5 provinces in Region 2</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Accomplishment Rate</span>
                    <span style="color: var(--scale-success); font-weight: 700; font-size: 0.85rem; font-family: var(--font-mono);">
                        {{ summary.completion_rate }}%
                    </span>
                </div>
                <div class="metric-value">{{ summary.operational_sites }} <span style="font-size: 0.95rem; font-weight: 500; color: var(--c-ink-muted);">/ {{ summary.total_sites }}</span></div>
                <div class="metric-subtext">Avg cycle time: {{ summary.average_cycle_time }} days</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">LGU Scope Coverage</span>
                    <span class="badge badge-kind-b">{{ summary.scope_coverage.coverage_pct }}%</span>
                </div>
                <div class="metric-value">{{ summary.scope_coverage.covered_lgus }} / {{ summary.scope_coverage.total_lgus }}</div>
                <div class="metric-subtext">Cities & municipalities with $\ge 1$ site</div>
            </div>

            <div class="metric-card">
                <div class="metric-card-top">
                    <span class="metric-label">Beneficiaries (GAD)</span>
                    <span class="badge badge-kind-c">{{ gad.female_percentage }}% F</span>
                </div>
                <div class="metric-value">{{ formatNumber(gad.total_beneficiaries) }}</div>
                <div class="metric-subtext">{{ formatNumber(gad.female_count) }} Female • {{ formatNumber(gad.male_count) }} Male</div>
            </div>
        </div>

        <!-- Backlog & Aging Tracking Alert Ribbon -->
        <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--scale-danger); padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 700; font-size: 0.95rem; color: var(--c-ink);">Target Overdue Backlog & Aging Analysis</span>
                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: var(--scale-danger); font-family: var(--font-mono);">
                            {{ backlog.total_backlog }} Overdue Sites
                        </span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
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

        <!-- Two-Column Analytics Grid: Provincial Leaderboard + Burn-Up Trajectory -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <!-- Provincial Accomplishment Leaderboard -->
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
                <div class="card-body" style="padding: 1rem 1.25rem;">
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div v-for="prov in provincialLeaderboard" :key="prov.psgc"
                             style="padding-bottom: 0.75rem; border-bottom: 1px solid var(--c-border);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-weight: 600; color: var(--c-ink); font-size: 0.88rem;">{{ prov.name }}</span>
                                    <span style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--c-ink-muted);">{{ prov.completed_sites }}/{{ prov.total_sites }} sites</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <span v-if="prov.backlog_sites > 0"
                                          style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--scale-danger); background: rgba(239, 68, 68, 0.1); padding: 0.1rem 0.35rem; border-radius: var(--radius-sm);">
                                        {{ prov.backlog_sites }} backlog
                                    </span>
                                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem;"
                                          :style="{ color: getProgressColor(prov.completion_rate) }">
                                        {{ prov.completion_rate }}%
                                    </span>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div style="height: 6px; background: var(--c-border); border-radius: var(--radius-full); overflow: hidden;">
                                <div :style="{ width: prov.completion_rate + '%', background: getProgressColor(prov.completion_rate), height: '100%', transition: 'width 0.4s ease' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Burn-Up Trajectory / Accomplishment Trend -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Burn-Up Trajectory & Milestone Velocity</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Monthly completed sites vs. planned targets
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem; font-family: var(--font-mono);">
                        <span style="display: flex; align-items: center; gap: 0.3rem;">
                            <span style="width: 10px; height: 10px; background: var(--scale-success); border-radius: 2px;"></span> Actual
                        </span>
                        <span style="display: flex; align-items: center; gap: 0.3rem;">
                            <span style="width: 10px; height: 10px; background: var(--c-ink-muted); border-radius: 2px;"></span> Planned
                        </span>
                    </div>
                </div>
                <div class="card-body" style="padding: 1.25rem;">
                    <!-- Trajectory Visual Bars -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; height: 180px; padding-top: 1rem; border-bottom: 1px solid var(--c-border);">
                        <div v-for="(label, idx) in trajectory.labels" :key="label"
                             style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.4rem; height: 100%; justify-content: flex-end;">
                            <div style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--c-ink); font-weight: 600;">
                                {{ trajectory.actual[idx] }}
                            </div>
                            <div style="display: flex; gap: 4px; align-items: flex-end; height: 120px; width: 36px; justify-content: center;">
                                <!-- Planned Bar -->
                                <div :style="{ height: getBarHeight(trajectory.planned[idx]) + '%', width: '12px', background: 'var(--c-border-hover)', borderRadius: '2px 2px 0 0' }"
                                     title="Planned Target"></div>
                                <!-- Actual Bar -->
                                <div :style="{ height: getBarHeight(trajectory.actual[idx]) + '%', width: '12px', background: 'var(--scale-success)', borderRadius: '2px 2px 0 0' }"
                                     title="Actual Completed"></div>
                            </div>
                            <div style="font-size: 0.7rem; font-family: var(--font-mono); color: var(--c-ink-muted); white-space: nowrap;">
                                {{ label.split(' ')[0] }}
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--c-ink-muted); margin-top: 0.75rem;">
                        <span>Progress is pacing within scheduled variance</span>
                        <span style="font-family: var(--font-mono); color: var(--scale-success); font-weight: 600;">Velocity: Steady</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Tracking Archetypes Showcase -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <div>
                    <div class="card-title">4 Portfolio Tracking Archetypes</div>
                    <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                        Configurable engines designed to track diverse DICT mandates without custom coding
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid-4">
                    <!-- Kind A -->
                    <div style="padding: 1rem; border-radius: var(--radius-md); background: var(--kind-a-bg); border: 1px solid var(--kind-a-border);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span class="badge badge-kind-a">Kind A</span>
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--kind-a);">
                                {{ archetypeStats.kind_a.sites_count }} Sites
                            </span>
                        </div>
                        <h4 style="font-size: 0.92rem; margin-bottom: 0.35rem;">Point Pins & Clusters</h4>
                        <p style="font-size: 0.78rem; color: var(--c-ink-secondary); line-height: 1.4;">
                            Physical sites with install → operate lifecycles. Free Wi-Fi, GovNet, PIALEOS, LEO Satellite.
                        </p>
                    </div>

                    <!-- Kind B -->
                    <div style="padding: 1rem; border-radius: var(--radius-md); background: var(--kind-b-bg); border: 1px solid var(--kind-b-border);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span class="badge badge-kind-b">Kind B</span>
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--kind-b);">
                                {{ archetypeStats.kind_b.sites_count }} Onboarded
                            </span>
                        </div>
                        <h4 style="font-size: 0.92rem; margin-bottom: 0.35rem;">Choropleth Adoption</h4>
                        <p style="font-size: 0.78rem; color: var(--c-ink-secondary); line-height: 1.4;">
                            LGUs and agencies adopting software/services. eLGU, eGovPay, eGovPH, GovMail, GovCloud.
                        </p>
                    </div>

                    <!-- Kind C -->
                    <div style="padding: 1rem; border-radius: var(--radius-md); background: var(--kind-c-bg); border: 1px solid var(--kind-c-border);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span class="badge badge-kind-c">Kind C</span>
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--kind-c);">
                                {{ archetypeStats.kind_c.sites_count }} Cohorts
                            </span>
                        </div>
                        <h4 style="font-size: 0.92rem; margin-bottom: 0.35rem;">Activities & Headcounts</h4>
                        <p style="font-size: 0.78rem; color: var(--c-ink-secondary); line-height: 1.4;">
                            Trainings and distributions with sex-disaggregated (M/F) GAD indicators. DigitalJobsPH, ILCDB.
                        </p>
                    </div>

                    <!-- Kind D -->
                    <div style="padding: 1rem; border-radius: var(--radius-md); background: var(--kind-d-bg); border: 1px solid var(--kind-d-border);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span class="badge badge-kind-d">Kind D</span>
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--kind-d);">
                                {{ archetypeStats.kind_d.sites_count }} Studies
                            </span>
                        </div>
                        <h4 style="font-size: 0.92rem; margin-bottom: 0.35rem;">Milestones & Sweeps</h4>
                        <p style="font-size: 0.78rem; color: var(--c-ink-secondary); line-height: 1.4;">
                            Field sweeps and strategic deliverables without fixed coordinates. ICT Household Survey, NICTEF.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Programs Directory & Recent Activity Row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 1.5rem;">
            <!-- Active Programs Preview Table -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Priority Programs Directory</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Live accomplishments calculated from field site telemetry
                        </div>
                    </div>
                    <Link href="/projects" class="btn btn-outline btn-sm">View All {{ summary.total_projects }}</Link>
                </div>
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Initiative</th>
                                <th>Kind</th>
                                <th>Accomplishment</th>
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
                                    <span :class="'badge badge-kind-' + proj.kind.toLowerCase()">Kind {{ proj.kind }}</span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-family: var(--font-mono); font-weight: 600; font-size: 0.82rem; width: 40px;">{{ proj.percent }}%</span>
                                        <div style="width: 70px; height: 5px; background: var(--c-border); border-radius: var(--radius-full); overflow: hidden;">
                                            <div :style="{ width: proj.percent + '%', background: getProgressColor(proj.percent), height: '100%' }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <Link :href="'/map?project_id=' + proj.id" class="btn btn-ghost btn-sm">Map</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Verified Field Reports & Timeline Activity -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Recent Field Telemetry & Verification</div>
                        <div style="font-size: 0.78rem; color: var(--c-ink-muted); margin-top: 0.2rem;">
                            Append-only milestones submitted by field engineers
                        </div>
                    </div>
                    <Link href="/reviews" class="btn btn-outline btn-sm">QA Queue</Link>
                </div>
                <div class="card-body" style="padding: 0.5rem 1.25rem;">
                    <div v-if="recentInterventions.length === 0" style="padding: 2rem; text-align: center; color: var(--c-ink-muted);">
                        No recent field interventions recorded yet.
                    </div>
                    <div v-else style="display: flex; flex-direction: column;">
                        <div v-for="inv in recentInterventions" :key="inv.id"
                             style="padding: 0.75rem 0; border-bottom: 1px solid var(--c-border); display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-family: var(--font-mono); font-size: 0.78rem; font-weight: 700; color: var(--c-ink);">{{ inv.site_code }}</span>
                                    <span class="badge" :class="getVerificationBadgeClass(inv.verification_status)">{{ inv.verification_status }}</span>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--c-ink); margin-top: 0.15rem;">
                                    {{ inv.intervention_type }} • <span style="color: var(--c-ink-muted);">{{ inv.location_name }}</span>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--c-ink-muted); font-family: var(--font-mono);">
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
