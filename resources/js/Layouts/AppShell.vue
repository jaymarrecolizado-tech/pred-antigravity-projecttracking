<template>
    <div class="app-shell" :data-theme="currentTheme">
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div 
            class="sidebar-backdrop" 
            :class="{ active: isMobileMenuOpen }" 
            @click="isMobileMenuOpen = false"
        ></div>

        <!-- Sidebar Navigation -->
        <aside class="app-sidebar" :class="{ 'mobile-open': isMobileMenuOpen }">
            <div class="sidebar-header">
                <Link href="/" style="text-decoration: none;">
                    <ReconLogo size="md" subtitle="DICT REGION 2" :withGovBadge="true" />
                </Link>
                <button 
                    type="button" 
                    class="mobile-close-btn" 
                    @click="isMobileMenuOpen = false"
                    aria-label="Close navigation"
                >
                    ✕
                </button>
            </div>

            <!-- Sleek Region Status Pill -->
            <div style="padding: 0.55rem 0.85rem; margin: 0.75rem 1rem 0.35rem 1rem; background: var(--c-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--c-border-subtle); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.74rem; font-weight: 600; color: var(--c-ink); display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--scale-success); display: inline-block;"></span>
                    Free Wi-Fi Project
                </span>
                <span style="font-size: 0.68rem; font-family: var(--font-mono); color: var(--c-ink-muted);">1,868 Sites • 5 Prov</span>
            </div>

            <!-- Clean Navigation Sections -->
            <nav class="sidebar-nav">
                <!-- 1. Main Workspace -->
                <div class="nav-section">
                    <div class="nav-section-title">Workspace</div>
                    <Link href="/" class="nav-item" :class="{ active: currentRoute === 'dashboard' || currentRoute === '' }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </Link>
                    <Link href="/map" class="nav-item" :class="{ active: currentRoute.startsWith('map') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon>
                                <line x1="9" x2="9" y1="3" y2="18"></line>
                                <line x1="15" x2="15" y1="6" y2="21"></line>
                            </svg>
                        </span>
                        <span>Free Wi-Fi Map</span>
                        <span class="nav-badge" style="background: var(--c-primary-subtle); color: var(--c-primary); font-weight: 700;">1,868</span>
                    </Link>
                </div>

                <!-- 2. Operations & Field -->
                <div class="nav-section">
                    <div class="nav-section-title">Operations & Field</div>
                    <Link href="/projects" class="nav-item" :class="{ active: currentRoute.startsWith('projects') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                            </svg>
                        </span>
                        <span>Lots & Contracts</span>
                    </Link>
                    <Link href="/sites" class="nav-item" :class="{ active: currentRoute.startsWith('sites') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </span>
                        <span>Wi-Fi Site Registry</span>
                    </Link>
                    <Link href="/institutions" class="nav-item" :class="{ active: currentRoute.startsWith('institutions') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 20V10"></path>
                                <path d="M12 20V4"></path>
                                <path d="M6 20v-6"></path>
                            </svg>
                        </span>
                        <span>LGUs & Locations</span>
                    </Link>
                    <Link href="/submissions/create" class="nav-item" :class="{ active: currentRoute.startsWith('submissions') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14"></path>
                            </svg>
                        </span>
                        <span>Submit Site Report</span>
                    </Link>
                    <Link href="/reviews" class="nav-item" :class="{ active: currentRoute.startsWith('reviews') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"></path>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                            </svg>
                        </span>
                        <span>Verification Queue</span>
                    </Link>
                    <Link href="/reports" class="nav-item" :class="{ active: currentRoute.startsWith('reports') }">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" x2="18" y1="20" y2="10"></line>
                                <line x1="12" x2="12" y1="20" y2="4"></line>
                                <line x1="6" x2="6" y1="20" y2="14"></line>
                            </svg>
                        </span>
                        <span>Reports & Exports</span>
                    </Link>
                </div>

                <!-- 3. Administration (Collapsible Accordion) -->
                <div class="nav-section">
                    <button 
                        type="button" 
                        class="nav-section-title" 
                        @click="isAdminOpen = !isAdminOpen"
                        style="background: transparent; border: none; cursor: pointer; display: flex; align-items: center; justify-content: space-between; width: 100%; text-align: left;"
                    >
                        <span>Administration</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ transform: isAdminOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <div v-show="isAdminOpen" style="display: flex; flex-direction: column; gap: 0.2rem;">
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
                        <Link href="/admin/users" class="nav-item" :class="{ active: currentRoute.startsWith('admin/users') }">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </span>
                            <span>Users & Access</span>
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
                        <Link href="/admin/project-types" class="nav-item" :class="{ active: currentRoute.startsWith('admin/project-types') }">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                            </span>
                            <span>Workflow Builder</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="sidebar-footer">
                <span>RECON Platform</span>
                <span>Region 02</span>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-canvas">
            <!-- Topbar -->
            <header class="topbar">
                <button 
                    type="button" 
                    class="mobile-hamburger-btn" 
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    aria-label="Toggle navigation menu"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

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
import { Link, usePage, router } from '@inertiajs/vue3';
import ReconLogo from '@/Components/Brand/ReconLogo.vue';

const page = usePage();
const searchQuery = ref('');
const currentTheme = ref('light');

const currentRoute = computed(() => {
    return page.url.replace(/^\//, '');
});

const isAdminOpen = ref(false);
const isMobileMenuOpen = ref(false);

router.on('navigate', () => {
    isMobileMenuOpen.value = false;
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
    isAdminOpen.value = currentRoute.value.startsWith('admin');
});
</script>
