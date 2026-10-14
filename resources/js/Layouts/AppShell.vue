<template>
    <div class="app-shell" :data-theme="currentTheme">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <div class="brand-badge">NMS·V2</div>
                <div class="brand-info">
                    <div class="brand-title">DICT Region 2</div>
                    <div class="brand-subtitle">Rollout Tracker</div>
                </div>
            </div>

            <!-- Program Switcher Card (NMS Reference) -->
            <div class="sidebar-program-card">
                <div class="program-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </div>
                <div class="program-card-text">
                    <div class="program-card-name">All Programs (R2)</div>
                    <div class="program-card-stats">29 Active • 5 Provinces</div>
                </div>
            </div>

            <!-- Navigation Sections -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Operations</div>
                    <Link href="/" class="nav-item" :class="{ active: currentRoute === 'dashboard' || currentRoute === '' }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                            </svg>
                        </span>
                        <span>Live Dashboard</span>
                    </Link>
                    <Link href="/map" class="nav-item" :class="{ active: currentRoute.startsWith('map') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon>
                                <line x1="9" x2="9" y1="3" y2="18"></line>
                                <line x1="15" x2="15" y1="6" y2="21"></line>
                            </svg>
                        </span>
                        <span>Interactive Map</span>
                        <span class="nav-badge">R2</span>
                    </Link>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Portfolio & Scope</div>
                    <Link href="/projects" class="nav-item" :class="{ active: currentRoute.startsWith('projects') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m16 6 4 14"></path>
                                <path d="M12 6v14"></path>
                                <path d="M8 8v12"></path>
                                <path d="M4 4v16"></path>
                            </svg>
                        </span>
                        <span>Projects Directory</span>
                        <span class="nav-badge">29</span>
                    </Link>
                    <Link href="/sites" class="nav-item" :class="{ active: currentRoute.startsWith('sites') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </span>
                        <span>Site Registry</span>
                    </Link>
                    <Link href="/institutions" class="nav-item" :class="{ active: currentRoute.startsWith('institutions') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 20V10"></path>
                                <path d="M12 20V4"></path>
                                <path d="M6 20v-6"></path>
                            </svg>
                        </span>
                        <span>Institutions (LGUs/SUCs)</span>
                    </Link>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Field & Accomplishment</div>
                    <Link href="/submissions/create" class="nav-item" :class="{ active: currentRoute.startsWith('submissions') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" x2="8" y1="13" y2="13"></line>
                                <line x1="16" x2="8" y1="17" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </span>
                        <span>Field Submissions</span>
                    </Link>
                    <Link href="/reviews" class="nav-item" :class="{ active: currentRoute.startsWith('reviews') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <polyline points="9 12 11 14 15 10"></polyline>
                            </svg>
                        </span>
                        <span>Verification Queue</span>
                    </Link>
                    <Link href="/reports" class="nav-item" :class="{ active: currentRoute.startsWith('reports') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" x2="12" y1="20" y2="10"></line>
                                <line x1="18" x2="18" y1="20" y2="4"></line>
                                <line x1="6" x2="6" y1="20" y2="16"></line>
                            </svg>
                        </span>
                        <span>Accomplishment Reports</span>
                    </Link>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Administration</div>
                    <Link href="/admin/geo" class="nav-item" :class="{ active: currentRoute.startsWith('admin/geo') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" x2="22" y1="12" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                        </span>
                        <span>PSGC & Districts</span>
                    </Link>
                    <Link href="/admin/project-types" class="nav-item" :class="{ active: currentRoute.startsWith('admin/project-types') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </span>
                        <span>Workflow Builder</span>
                    </Link>
                    <Link href="/admin/users" class="nav-item" :class="{ active: currentRoute.startsWith('admin/users') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                        <span>Users & Grants</span>
                    </Link>
                    <Link href="/admin/audit" class="nav-item" :class="{ active: currentRoute.startsWith('admin/audit') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span>Audit Trail</span>
                    </Link>
                </div>
            </nav>

            <div class="sidebar-footer">
                <span>Region 2 Hub</span>
                <span>v2.0 • Active</span>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-canvas">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-search">
                    <svg class="topbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
                    </svg>
                    <input type="text" placeholder="Search sites, programs, contracts, LGUs..." v-model="searchQuery" />
                </div>

                <div class="topbar-actions">
                    <!-- Theme Toggle Button -->
                    <button class="btn btn-ghost btn-icon" @click="toggleTheme" :title="'Switch to ' + (currentTheme === 'dark' ? 'Light' : 'Dark') + ' theme'">
                        <svg v-if="currentTheme === 'dark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" x2="12" y1="1" y2="3"></line>
                            <line x1="12" x2="12" y1="21" y2="23"></line>
                            <line x1="4.22" x2="5.64" y1="4.22" y2="5.64"></line>
                            <line x1="18.36" x2="19.78" y1="18.36" y2="19.78"></line>
                            <line x1="1" x2="3" y1="12" y2="12"></line>
                            <line x1="21" x2="23" y1="12" y2="12"></line>
                            <line x1="4.22" x2="5.64" y1="19.78" y2="18.36"></line>
                            <line x1="18.36" x2="19.78" y1="5.64" y2="4.22"></line>
                        </svg>
                        <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>

                    <!-- User Profile Quick Badge -->
                    <div class="user-profile-btn">
                        <div class="user-avatar">
                            <span>R2</span>
                        </div>
                        <div class="user-meta">
                            <span class="user-name">Regional Admin</span>
                            <span class="user-role">Super Admin • 2FA Active</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content Slot -->
            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const searchQuery = ref('');
const currentTheme = ref('light');

const currentRoute = computed(() => {
    return page.url.replace(/^\//, '');
});

function toggleTheme() {
    currentTheme.value = currentTheme.value === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme.value);
    localStorage.setItem('dict_tracker_theme', currentTheme.value);
}

onMounted(() => {
    const savedTheme = localStorage.getItem('dict_tracker_theme');
    if (savedTheme) {
        currentTheme.value = savedTheme;
    } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        currentTheme.value = prefersDark ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', currentTheme.value);
});
</script>
