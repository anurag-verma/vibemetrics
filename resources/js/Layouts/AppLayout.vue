<script setup>
import FlashToast from '@/Components/FlashToast.vue';
import Logo from '@/Components/Logo.vue';
import SidebarCollapseButton from '@/Components/SidebarCollapseButton.vue';
import SidebarUserMenu from '@/Components/SidebarUserMenu.vue';
import { useSidebar } from '@/Composables/useSidebar';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    site: { type: Object, default: null },
    range: { type: Number, default: null },
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
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-slate-50 dark:bg-slate-950">
        <FlashToast />

        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-50 flex h-screen shrink-0 flex-col border-r border-slate-800 bg-slate-900 transition-all duration-200 lg:static lg:translate-x-0"
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                isDesktopCollapsed ? 'w-64 lg:w-[4.5rem]' : 'w-64',
            ]"
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
                <SidebarCollapseButton
                    :collapsed="isDesktopCollapsed"
                    dark-sidebar
                    @toggle="toggleCollapsed"
                />
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
                    <svg
                        class="h-4 w-4 shrink-0"
                        :class="isDesktopCollapsed ? 'lg:block' : 'lg:hidden'"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span :class="isDesktopCollapsed ? 'lg:hidden' : ''">← Admin</span>
                </Link>
                <SidebarUserMenu
                    :email="user?.email"
                    variant="user"
                    dark-sidebar
                    :collapsed="isDesktopCollapsed"
                />
            </div>
        </aside>

        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <header class="z-30 flex h-14 shrink-0 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 lg:px-6">
                <button type="button" class="rounded-lg p-2 text-slate-500 lg:hidden" @click="sidebarOpen = true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>

                <template v-if="showSiteToolbar && site">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <select
                            class="vm-input max-w-[200px] py-1.5 text-sm"
                            :value="site.id"
                            @change="switchSite(Number($event.target.value))"
                        >
                            <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <Link :href="route('sites.edit', site.id)" class="vm-btn-secondary hidden sm:inline-flex">Edit</Link>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="route('sites.export', { site: site.id, range: range ?? 30 })" class="vm-btn-secondary hidden sm:inline-flex">Export</a>
                    </div>
                </template>
                <template v-else>
                    <div class="flex-1">
                        <slot name="header" />
                    </div>
                </template>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
