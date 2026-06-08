<script setup>
import FlashToast from '@/Components/FlashToast.vue';
import Logo from '@/Components/Logo.vue';
import { useBranding } from '@/Composables/useBranding';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const { branding } = useBranding();
const showingNavigationDropdown = ref(false);
const isAdmin = computed(() => page.props.auth.user?.is_admin);
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <FlashToast />

        <nav class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center gap-8">
                        <Logo size="nav" href="/dashboard" />

                        <div class="hidden sm:flex sm:gap-1">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard') || route().current('sites.show')">
                                Analytics
                            </NavLink>
                            <NavLink :href="route('sites.index')" :active="route().current('sites.*') && !route().current('sites.show')">
                                Sites
                            </NavLink>
                            <NavLink v-if="isAdmin" :href="route('admin.dashboard')" :active="route().current('admin.*')">
                                Admin
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <img :src="branding.siteLogoUrl" :alt="branding.appName" class="h-9 w-9 rounded-lg object-contain" />
                                    {{ $page.props.auth.user.name }}
                                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="flex items-center sm:hidden">
                        <button type="button" class="rounded-lg p-2 text-slate-500" @click="showingNavigationDropdown = !showingNavigationDropdown">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div v-show="showingNavigationDropdown" class="border-t border-slate-100 sm:hidden">
                <div class="space-y-1 px-4 py-3">
                    <ResponsiveNavLink :href="route('dashboard')">Analytics</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('sites.index')">Sites</ResponsiveNavLink>
                    <ResponsiveNavLink v-if="isAdmin" :href="route('admin.dashboard')">Admin</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log out</ResponsiveNavLink>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="border-b border-slate-200/80 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
