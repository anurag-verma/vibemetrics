<script setup>
import AnnouncementBanner from '@/Components/AnnouncementBanner.vue';
import AppVersion from '@/Components/AppVersion.vue';
import FlashToast from '@/Components/FlashToast.vue';
import Logo from '@/Components/Logo.vue';
import SidebarCollapseButton from '@/Components/SidebarCollapseButton.vue';
import SidebarUserMenu from '@/Components/SidebarUserMenu.vue';
import { useBodyScrollLock } from '@/Composables/useBodyScrollLock';
import { useEscapeKey } from '@/Composables/useEscapeKey';
import { useSidebar } from '@/Composables/useSidebar';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    site: { type: Object, default: null },
    dateRange: { type: Object, default: null },
    showSiteToolbar: { type: Boolean, default: false },
});

const page = usePage();
const sidebarOpen = ref(false);
const { collapsed, toggle: toggleCollapsed } = useSidebar();

const user = computed(() => page.props.auth.user);
const sites = computed(() => page.props.auth.sites ?? []);
const isAdmin = computed(() => user.value?.is_admin);
const isDesktopCollapsed = computed(() => collapsed.value);

const overviewHref = computed(() => {
    if (props.site) {
        return route('sites.show', props.site.id);
    }

    if (sites.value.length) {
        return route('sites.show', sites.value[0].id);
    }

    return route('dashboard');
});

const switchSite = (siteId) => {
    router.visit(route('sites.show', siteId));
    sidebarOpen.value = false;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

useBodyScrollLock(sidebarOpen);
useEscapeKey(sidebarOpen, closeSidebar);
</script>

<template>
    <div class="app-shell flex h-full flex-col overflow-hidden bg-slate-50 dark:bg-slate-950">
        <FlashToast />
        <AnnouncementBanner />

        <div class="flex min-h-0 flex-1 overflow-hidden">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            aria-hidden="true"
            @click="closeSidebar"
        />

        <aside
            id="app-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex h-full shrink-0 flex-col border-r border-slate-800 bg-slate-900 transition-all duration-200 lg:static lg:translate-x-0"
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                isDesktopCollapsed ? 'w-64 lg:w-[4.5rem]' : 'w-64',
            ]"
            :role="sidebarOpen ? 'dialog' : 'navigation'"
            :aria-modal="sidebarOpen ? 'true' : undefined"
            aria-label="App navigation"
        >
            <div
                class="relative flex h-14 shrink-0 items-center border-b border-slate-800"
                :class="isDesktopCollapsed ? 'justify-between px-2 lg:justify-center' : 'justify-between px-4'"
            >
                <Logo
                    size="nav"
                    href="/dashboard"
                    :class="isDesktopCollapsed ? 'lg:hidden' : ''"
                />
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden"
                        aria-label="Close navigation menu"
                        @click="closeSidebar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <SidebarCollapseButton
                        :collapsed="isDesktopCollapsed"
                        dark-sidebar
                        @toggle="toggleCollapsed"
                    />
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto overflow-x-hidden p-3">
                <Link
                    :href="overviewHref"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('sites.show') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Overview' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Overview</span>
                </Link>
                <Link
                    :href="route('sites.index')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('sites.index') || route().current('sites.create') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Websites' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Websites</span>
                </Link>
                <Link
                    :href="route('documentation')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('documentation') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Documentation' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Documentation</span>
                </Link>
            </nav>

            <div class="shrink-0 space-y-2 overflow-visible border-t border-slate-800 p-3">
                <Link
                    v-if="isAdmin"
                    :href="route('admin.dashboard')"
                    class="vm-sidebar-link-dark text-xs"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.*') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Admin' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Admin</span>
                </Link>
                <SidebarUserMenu
                    :email="user?.email"
                    variant="user"
                    dark-sidebar
                    :collapsed="isDesktopCollapsed"
                />
                <AppVersion :collapsed="isDesktopCollapsed" />
            </div>
        </aside>

        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <header
                v-if="showSiteToolbar && site"
                class="z-30 flex min-h-14 shrink-0 flex-wrap items-center gap-2 border-b border-slate-200 bg-white/90 px-4 py-2 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 sm:gap-3 sm:py-2 lg:px-6"
            >
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 lg:hidden"
                    :aria-expanded="sidebarOpen"
                    aria-controls="app-sidebar"
                    aria-label="Open navigation menu"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>

                <div class="flex min-w-0 flex-1 basis-[calc(100%-2.5rem)] items-center gap-2 sm:basis-auto sm:gap-3">
                    <select
                        class="vm-input min-w-0 max-w-full flex-1 py-1.5 text-sm sm:max-w-[200px] sm:flex-none"
                        :value="site.id"
                        @change="switchSite(Number($event.target.value))"
                    >
                        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <Link :href="route('sites.edit', site.id)" class="vm-btn-secondary shrink-0">Edit</Link>
                </div>
                <div class="flex w-full items-center gap-2 sm:ml-auto sm:w-auto">
                    <a
                        :href="route('sites.export', {
                            site: site.id,
                            preset: dateRange?.preset ?? 'last_30_days',
                            ...(dateRange?.preset === 'custom' ? { from: dateRange.from, to: dateRange.to } : {}),
                        })"
                        class="vm-btn-secondary flex-1 text-center sm:flex-none sm:text-left"
                    >Export</a>
                </div>
            </header>

            <header
                v-else
                class="z-30 flex h-14 shrink-0 items-center border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 lg:hidden"
            >
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500"
                    :aria-expanded="sidebarOpen"
                    aria-controls="app-sidebar"
                    aria-label="Open navigation menu"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <slot />
            </main>
        </div>
        </div>
    </div>
</template>
