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
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const sidebarOpen = ref(false);
const { collapsed, toggle: toggleCollapsed } = useSidebar();
const user = computed(() => page.props.auth.user);
const isDesktopCollapsed = computed(() => collapsed.value);

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
            id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex h-full shrink-0 flex-col border-r border-slate-200 bg-slate-900 transition-all duration-200 dark:border-slate-800 lg:static lg:translate-x-0"
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                isDesktopCollapsed ? 'w-64 lg:w-[4.5rem]' : 'w-64',
            ]"
            :role="sidebarOpen ? 'dialog' : 'navigation'"
            :aria-modal="sidebarOpen ? 'true' : undefined"
            aria-label="Admin navigation"
        >
            <div
                class="relative flex h-14 shrink-0 items-center border-b border-slate-800"
                :class="isDesktopCollapsed ? 'justify-between px-2 lg:justify-center' : 'justify-between px-4'"
            >
                <Logo
                    size="nav"
                    href="/admin"
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
                    :href="route('admin.dashboard')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.dashboard') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Overview' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Overview</span>
                </Link>
                <Link
                    :href="route('admin.sites.index')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.sites.*') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Sites' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Sites</span>
                </Link>
                <Link
                    :href="route('admin.health.index')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.health.*') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Health' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Health</span>
                </Link>
                <Link
                    :href="route('admin.logs.index')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.logs.*') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Logs' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Logs</span>
                </Link>
                <Link
                    :href="route('admin.users.index')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.users.*') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Users' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Users</span>
                </Link>
                <Link
                    :href="route('admin.settings.index')"
                    class="vm-sidebar-link-dark"
                    :class="[
                        { 'vm-sidebar-link-dark-active': route().current('admin.settings.*') },
                        isDesktopCollapsed ? 'lg:justify-center lg:px-2' : '',
                    ]"
                    :title="isDesktopCollapsed ? 'Settings' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">Settings</span>
                </Link>
            </nav>

            <div class="shrink-0 space-y-2 overflow-visible border-t border-slate-800 p-3">
                <Link
                    :href="route('dashboard')"
                    class="vm-sidebar-link-dark text-xs"
                    :class="isDesktopCollapsed ? 'lg:justify-center lg:px-2' : ''"
                    :title="isDesktopCollapsed ? 'User app' : undefined"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">User app</span>
                </Link>
                <SidebarUserMenu
                    :email="user?.email"
                    variant="admin"
                    dark-sidebar
                    :collapsed="isDesktopCollapsed"
                />
                <AppVersion :collapsed="isDesktopCollapsed" />
            </div>
        </aside>

        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <header class="z-30 flex h-14 shrink-0 items-center border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 lg:hidden">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500"
                    :aria-expanded="sidebarOpen"
                    aria-controls="admin-sidebar"
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
